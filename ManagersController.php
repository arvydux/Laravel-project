<?php

namespace App\Http\Controllers\API\REST;

use App\Http\Controllers\APIController;
use App\Http\Controllers\WebController;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\JsonableInterface;
use App\Models\Staff;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ManagersController extends APIController
{
    public function index(Request $request)
    {
        //defaults
        $sidx = $request->input('sidx') ? $request->input('sidx') : 'id';
        $sord = $request->input('sord') ? $request->input('sord') : 'asc';
        $rows = $request->input('rows') ? $request->input('rows') : 10;


        $filter = [
            'name' => 'ilike',
            'code' => 'ilike',
            'phone' => 'ilike',
            'email' => 'ilike',
            'city' => 'ilike',
            'address' => 'ilike',
        ];

        $managers = $request->input('group') ?
            Staff::whereHas('groups', function($q) use ($request) {
                $q->where('id', $request->input('group'));
            }) : Staff::query();

        $managers->where(function($q) use($request, $filter){
            foreach($filter as $c => $w) {
                if($request->input($c)) {
                    if($w=='ilike')		$q->where($c, $w, '%'.$request->input($c).'%');
                    else				$q->where($c, $w, $request->input($c));
                }
            }
        });

        return $managers
            ->where('type','manager')
            ->with('groups')
            ->with('users')
            ->orderBy($sidx,$sord)
            ->paginate($rows)
            ->toJson();
    }

    public function store(Request $request)
    {
        if(empty($request->input('id'))) abort(500,trans('errors.empty_id'));
        $manager=Staff::findOrFail($request->input('id'));

        //update all fields
        foreach($request->all() as $key=>$data)
        {
            if(in_array($key,self::getProtectedFields())) continue;

            //updating table
            if(in_array($key,self::getEditableFields()))
                $manager->{$key} = empty($data) ? null : $data;

            //updating group

            elseif($key=='group_id')
                $manager->groups()->sync(array($data));
        }

        $manager->save();

        return '';
    }

    public function create(Request $request)
    {
        $manager=new Staff();
        foreach(self::getEditableFields() as $field)
        {
            if(array_key_exists($field,$request->all()))
                $manager->{$field} = empty($request->input($field)) ? null : $request->input($field);
        }
        $sp=\Auth::getUser()->getServiceProviderID();
        if(empty($sp))	abort(500,trans('errors.empty_sp'));

        $manager->service_provider_id= $sp;
        $manager->type='manager';
        $manager->save();
        //updating group
        if($request->input('group_id'))
            $manager->groups()->attach(array($request->input('group_id')));

        $manager->save();
        return json_encode(
            [
                'message' => trans('core.record_added'),
                'data'=>$manager->id
            ]
        );

    }

    public function destroy($id)
    {
        $manager=Staff::findOrFail($id)->delete();
        return '';
    }


    // helper functions
    protected function getEditableFields() //TODO - make this name in all controllers
    {
        return array(
            "name",
            "code",
            "city",
            "phone",
            "email",
            "fax",
            "position",
            "address",
            "notes",
        );
    }

    protected function getProtectedFields()
    {
        return array(
            "id"
        );
    }

}