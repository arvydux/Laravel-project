<?php

namespace App\Models;

use App\Exceptions\Models\CardNotFoundException;
use App\Models\Generic\Groupable;
use App\Models\Generic\LocaleTrait;
use App\Models\Generic\Resoursable;
use App\Models\Generic\Transactions\BalanceTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cards extends Resoursable
{
    use Groupable, BalanceTrait, LocaleTrait;
    //protected $dates = ['deleted_at'];

    /**
     * Attributes to encrypt
     * @var array
     */
    protected $encryptable = ['pin'];

    protected $casts = [
        'valid_to'       => 'date',
        'valid_from'     => 'date',
        'fcredit'        => 'json'
    ];

    protected $appends = ['credit'];

    public function reservations() {
        return $this->hasMany(Reservations::class, 'card_number', 'number');
    }

    public function charge($amount)
    {

        if($this->day_credit_limit !== NULL) {
            if ($this->balance > 0) {
                if ($this->balance < $amount) {
                    $this->day_credit += $amount - $this->balance;
                }
            } else {
                $this->day_credit += $amount;
            }
        }

        //print_r('Day credit: ' . $this->day_credit);
        $this->balance -= $amount;

        return $this->save();
    }

    public function isActive()
    {
        $current = new Carbon();
        return ($this->status['value'] != 'blocked' && ( $current >= $this->valid_from && $this->valid_to >= $current)) && $this->countAllowed();
    }

    public function allowed(Transactions $t = null)
    {

        $current = new Carbon();
        if ($this->isActive()) {
            if($t) {
                if(!$this->creditFilterAllowed($t)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    public function countAllowed()
    {

        if($this->countLimitDay !== NULL && $this->countLimitDay !== 0) {

            if ($this->countLimit === NULL) {
                $this->countLimit = $this->countLimitDay;
            }

            if ($this->countLimit !== NULL) {
                return $this->countLimit > 0;
            }

        }
        return true;
    }

    public function countLimitDecrease()
    {
        if($this->countLimit !== NULL) {
            $this->countLimit = $this->countLimit - 1;

            return $this->save();
        }

        return true;
    }

    public function creditFilterAllowed(Transactions $t)
    {
        //error_log(print_r('@@@@@@@@@Card@@@@@@@',1));
        return $this->allowedItemsGroups($t) && $this->client->creditFilterAllowed($t);

    }

    public function allowance($compare = NULL)
    {
        //print_r('Card: ' . $this->number . ' ');
        //Minimal client allowance
        $cla = (float) $this->client->allowance();

        //Card allowance
        $limit = (float)  $this->getMaxAllowedAmount();

        //Card day allowance
        $cda = (float) $this->dayAllowance();

        /*
        print_r('cla: '.$cla. ' ');
        print_r('limit: '.$limit . ' ');
        print_r('cda: '.$cda. ' ');
        print_r('Allowed: ' . (float) min($cla, $limit, $cda) . ' ');
        print_r('!!! ' .  (float) $compare <= (float) min($cla, $limit, $cda) . ' !!!!');
        */
        return $compare ?
            //Return allowance (Boolean)
            (float) $compare <= (float) min($cla, $limit, $cda)
            //If nothing to compare then check the lower limit
            : (float) min($limit, $cla, $cda);
    }

    public function dayAllowance()
    {
        return (float) ($this->day_credit_limit === NULL ? $this->getMaxAllowedAmount() : $this->day_credit_limit - $this->day_credit);
    }

    public function transactions()
    {
        return $this->hasMany(Transactions::class, 'card_number', 'number');
    }

    /**
     * @return Clients
     */
    public function client() {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    /**
     * @param $number
     * @return Cards
     */

    public static function findOrFailByNumber($number) {
        $model = self::where('cards.number', $number)->first();
        if(is_null($model)) {
            throw (new CardNotFoundException)->setModel(self::class);
        }
        return $model;
    }

    /*
     * Mutators
     */

    public function getTypeAttribute($value) {
        return $this->getAttributeLocale($value);
    }

    public function getStatusAttribute($value) {
        return $this->getAttributeLocale($value);
    }

    public function getBlockReasonAttribute($value) {
        return $this->getAttributeLocale($value);
    }
}