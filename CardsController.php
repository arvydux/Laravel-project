<?php

namespace App\Http\Controllers\API\REST;

use App\Http\Controllers\APIController;
use App\Http\Controllers\WebController;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\JsonableInterface;
use App\Models\Cards;

class CardsController extends APIController
{
    public function index(Request $request)
    {
        //defaults
        $sidx = $request->input('sidx') ? $request->input('sidx') : 'id';
        $sord = $request->input('sord') ? $request->input('sord') : 'asc';
        $rows = $request->input('rows') ? $request->input('rows') : 10;

        $filter = [
            'id'=>'=',
            'number' => 'ilike',
            'type' => '=',
            'credit' => '=',
            'debit' => '=',
            'status' => '=',
            'valid_from' => '=',
            'valid_to' => '=',
            'block_reason' => '='
        ];

        $cards = $request->input('group') ?
            Cards::whereHas('groups', function($q) use ($request) {
                $q->where('id', $request->input('group'));
            }) : Cards::query();

        if($request->input('client'))
            $cards = Cards::where('client_id',$request->input('client'));

        $cards->where(function($q) use($request, $filter){
            foreach($filter as $c => $w) {
                if($request->input($c)) {
                    if($w=='ilike')		$q->where($c, $w, '%'.$request->input($c).'%');
                    else				$q->where($c, $w, $request->input($c));

                }
            }
        });

        return $cards
            ->with('groups')
            ->orderBy($sidx,$sord)
            ->paginate($rows)
            ->toJson();
    }

    public function store(Request $request)
    {
        if(empty($request->input('id')))  abort(500, 'missing ID');
        $card=Cards::findOrFail($request->input('id'));
        $defaults=self::getEditableFields();
        //update all fields
        foreach($request->all() as $key=>$data)
        {
            if(array_key_exists($key,self::getProtectedFields())) continue;

            //updating cards table
            if(array_key_exists($key,self::getEditableFields()))
            {
                //hook for day_credit_limit
                if($key=='day_credit_limit' && empty($data))
                {
                    $data='';
                }

                if($key=='countLimitDay')
                {
                    if($card->countLimitDay!=$data){

                        $card->countLimit=$data;
                    }

                }

                $card->{$key} = (trim($data)=='') ? $defaults[$key] : $data;
            }
            //updating group
            elseif($key=='group_id')
                $card->groups()->sync(array($data));

        }
        // TODO: fcredit field save needed here after Ilja fixes UI to properly work with store() method.
        // fcredit separate setter since we have selectable options here.
        if(array_key_exists('credit_filter', $request->all())) {
            if ($request->input('credit_filter') == 'none') {
                $card->fcredit = '';
            } else {
                if(array_key_exists('credit_' . $request->input('credit_filter') . '_filter', $request->all())) {
                    $card->fcredit =[
                        $request->input('credit_filter') => $request->input('credit_' . $request->input('credit_filter') . '_filter')
                    ];
                }
            }
        }

        if($card->rvalue !== NULL) {
            $card->balance = $card->rvalue;
        }

        $card->save();
        return '';
    }

    public function create(Request $request)	//@TODO - add check for empty required fields ?
    {
        $card=new Cards();
        $defaults=self::getAddableFields();
        foreach(self::getAddableFields() as $key=>$default)
        {
            if(array_key_exists($key,$request->all()))
                $card->{$key} = (trim($request->input($key))=='') ? $defaults[$key] : $request->input($key);
        }
        // fcredit separate setter since we have selectable options here.
        if(array_key_exists('credit_filter', $request->all())) {
            if(array_key_exists('credit_' . $request->input('credit_filter') . '_filter', $request->all())) {
                $card->fcredit = [
                    $request->input('credit_filter') => $request->input('credit_' . $request->input('credit_filter') . '_filter')
                ];
            }
        }
        $card->save();
        //updating group
        if($request->input('group_id'))
            $card->groups()->attach(array($request->input('group_id')));
        $card->save();
        return json_encode(
            [
                'message' => trans('core.record_added'),
                'data'=>$card->id
            ]
        );
    }

    public function destroy($id)
    {
        $card=Cards::findOrFail($id)->delete();
        return '';
    }


    // helper functions
    protected function getEditableFields()
    {
        return array(
            "status"=>'active',
            "type"=>NULL,
            "block_reason"=>NULL,
            "valid_from"=>NULL,
            "valid_to"=>NULL,
            "rvalue"=>NULL,
            "day_credit_limit"=>NULL,
            "credit_limit"=>0,
            "fcredit"=>NULL,
            "credit_allowance"=>0,
            "pin"=>NULL,
            "notes"=>NULL,
            "countLimit"=>NULL,
            "countLimitDay"=>NULL,
        );
    }
    protected function getAddableFields()
    {
        return array_merge(self::getEditableFields(),
            array(
                "number"=>NULL,
                "rvalue"=>0,
                "pin"=>NULL,
                "client_id"=>NULL,
                "balance"=>0,
                "countLimit"=>NULL,
            ));
    }
//test
    protected function getProtectedFields()
    {
        return array(
            "id",
            "day_credit"
        );
    }

}