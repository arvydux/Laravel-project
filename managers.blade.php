@extends('admin.layouts.default')
@section('content')
@section('body_class', 'managers_page')
@include('admin.includes.languages-ui')
<div class="row">
    <div class="col-xs-12">
        <table id="grid-table"></table>
        <div id="grid-pager"></div>
        <div class="space"></div>
        <div class="tabbable">
            <ul class="nav nav-tabs" id="myTab">
                <li class="active">
                    <a data-toggle="tab" href="#tab1" class="tabme">
                        <i class="blue ace-icon fa fa-user bigger-120"></i>Vadibininkas
                    </a>
                </li>
                <li>
                    <a data-toggle="tab" href="#tab2" class="tabme">
                        <i class="green ace-icon fa fa-user bigger-120"></i>Priklausantys klientai
                    </a>
                </li>
                <li>
                    <a data-toggle="tab" href="#users" class="tabme">
                        <i class="green ace-icon fa fa-user bigger-120"></i>Vartotojas
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div id="tab1" class="tab-pane fade in active">
                    <form class="form-horizontal tabform validateme" role="form" id="poses_form">
                        <div class="noshow">
                            <div>
                                <div>
                                    <input name="id" hidden="hidden" value=""/>
                                    <input type="hidden" name="user_email" value=""/>
                                    <input type="hidden" name="user_id" value=""/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-6 col-lg-4">
                                {{ Bhelper::field('name','Vardas','',true,true,true) }}
                                {{ Bhelper::field('code','Asmens kodas') }}
                                {{ Bhelper::field('phone','Tel.') }}
                                {{ Bhelper::field('fax','Faks.') }}
                                {{ Bhelper::field('email','El. paštas') }}
                                {{ Bhelper::field('city','Miestas') }}
                                {{ Bhelper::field('address','Adresas') }}
                            </div>
                            <div class="col-xs-6 col-lg-4">
                                {{ Bhelper::field('position','Pareigos') }}
                                {{ Bhelper::select('group_id','Grupė',$groupsDrop,true) }}
                                {{ Bhelper::field('notes','Pastabos') }}
                            </div>
                        </div>
                        <div class="clearfix form-actions">
                            <div class="col-md-3">
                                <i class="ace-icon fa fa-cog fa-2x fa-spin loadicon green bigger-230 fa-pull-right" style="position:relative; top:6px;"></i>
                            </div>
                            <div class="col-md-9">
                                <button class="btn btn-info formsave" type="submit">
                                    <i class="ace-icon fa fa-check bigger-110"></i>
                                    Išsaugoti
                                </button>
                                &nbsp; &nbsp; &nbsp;
                                <button class="btn formcancel" type="button">
                                    <i class="ace-icon fa fa-undo bigger-110"></i>
                                    Atšaukti
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="users" class="tab-pane fade">
                    @include('admin.includes.users.user_tab', ['correlation_type'=>'staff'])
                </div>
                <div id="tab2" class="tab-pane fade">
                    @include('admin.includes.managers.clients_tab')
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/js/custom/defaults.js"></script>
<script src="/js/custom/formhelper.js"></script>

<script type="text/javascript">

    $(function()  {
        conf = {
            gridmodel :
                [
                    {
                        name : 'id',
                        index : 'id',
                        label : 'ID',
                        width : 40,
                        sorttype : "int",
                        editable : false,
                        search : false
                    },
                    {
                        name : 'name',
                        index : 'name',
                        label : document.trans.get('table_header_name'),
                        width : 400
                    },
                    {
                        name : 'code',
                        index : 'code',
                        label : document.trans.get('table_header_personal_code'),
                        width : 400
                    },
                    {
                        name : 'phone',
                        index : 'phone',
                        label : document.trans.get('table_header_phone'),
                        width : 400
                    },
                    {
                        name : 'email',
                        index : 'email',
                        label : document.trans.get('table_header_email'),
                        width : 400
                    },
                    {
                        name : 'city',
                        index : 'city',
                        label : document.trans.get('table_header_city'),
                        width : 400
                    },
                    {
                        name : 'address',
                        index : 'address',
                        label : document.trans.get('table_header_address'),
                        width : 400
                    },
                    // Hidden fields.
                    {
                        name :'group_id',
                        hidden : true,
                        jsonmap:'groups.0.id'
                    },
                    {
                        name : 'user_email',
                        hidden : true
                    },
                    {
                        name : 'user_id',
                        hidden : true
                    },
                    {
                        name : 'fax',
                        hidden : true
                    },
                    {
                        name : 'notes',
                        hidden : true
                    },
                    {
                        name : 'position',
                        hidden : true
                    },
                ],
            grid_selector : "#grid-table",
            pager_selector : "#grid-pager",
            form_selector : ".tabform",
        };

        // This can slightly vary.
        user_creation_form_config = {
            correlation_type : 'staff'
        }

        // Basic inits.
        initGrid();
        clearForm();
        disableForm();
        initButtons();

        // This is needed to force update the lastSel field's email.
        // override
        // TODO: deal with Ilja on possible extension for formhelper.js to include such cases too.
        // based on config option for example
        rowSelected = function(id)
        {
            if(id){
                lastSel=id;
                var data=$(grid_selector).getRowData(id);
                //setFormType(data.type);
                setFormData(id,data);
                updateAllTabs(id);
                updateDelUrl();
            }
            $('.duallistbox').bootstrapDualListbox('refresh');
        }

        preprocessDataExtra = function(value) {
            if (value.users) {
                if(value.users.length > 0) {
                    value.user_email=value.users[0].email;
                    value.user_id=value.users[0].id;
                }
            }
        }

    });

</script>
@include('admin.includes.users.user_form_switcher_js')
@stop