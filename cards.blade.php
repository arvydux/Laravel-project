@extends('admin.layouts.default')
@section('content')
    <div class="row">
        <div class="col-xs-12">
            <table id="grid-table">
            </table>
            <div id="grid-pager"></div>
            <div class="space"></div>
            <div class="tabbable">
                <ul class="nav nav-tabs" id="myTab">
                    <li class="active"> <a data-toggle="tab" href="#cards" class="tabme" id="cardstab "><i class="blue ace-icon fa fa-credit-card bigger-120"></i> Kortelė</a></li>
                    <li> <a data-toggle="tab" href="#clients" class="tabme" id="clientstab"><i class="green ace-icon fa fa-user bigger-120"></i> Klientas</a></li>
                    <!--<li> <a data-toggle="tab" href="#turnover"><i class="purple ace-icon fa fa-refresh bigger-120"></i> Apyvarta</a></li>-->
                </ul>
                <div class="tab-content">
                    <div id="cards" class="tab-pane fade in active">
                        <form class="form-horizontal tabform validateme" role="form" id="card_form">
                            <div class="noshow">
                                <div>
                                    <div>
                                        <input name="id" hidden="hidden" value=""/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6 col-lg-4">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">Kortelė</h3>
                                        </div>
                                        <div class="panel-body">
                                            {{ Bhelper::field('number','Serijinis nr.','',false,true,true) }}
                                            @if(Entrust::can('admin-sp'))
                                                {{ Bhelper::field('pin','PIN kodas','payonly req',true,true,true) }}
                                            @else
                                                {{ Bhelper::field('pin','PIN kodas','payonly req',false,true,true) }}
                                            @endif
                                            {{ Bhelper::select('type','Tipas',$typeDrop,false,'',false,'',false,false,false,true) }}
                                            {{ Bhelper::select('status','Kortelės buklė',$statusDrop) }}
                                            {{ Bhelper::select('block_reason','Kodel blokuota',$reasonDrop,false,'',false,'',false,false,true) }}
                                            {{ Bhelper::field('valid_from','Galioja nuo','dates-picker cal',true,true,true) }}
                                            {{ Bhelper::field('valid_to','Galioja iki','dates-picker cal',true,true,true) }}
                                            {{ Bhelper::select('group_id','Grupė',$groupsDrop,true) }}
                                            {{ Bhelper::field('notes','Pastabos','',true,true) }}
                                            <div class="form-group" id="clwrap">
                                                <label class="col-sm-3 control-label no-padding-right" for="client_id">Klientas</label>
                                                <div class="col-sm-9">
                                                    <button class="btn btn-primary" type="button" id="find_client">Pasirinkti klientą</button>
                                                    <input name="client_id" hidden="hidden" value="" id="client_id"/>
                                                    <strong id="client_name" class="text-danger">Klientas nepasirinktas</strong> </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6 col-lg-4">
                                    <div class="panel panel-success" id="cardlimits">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">Limitai ir likučiai</h3>
                                        </div>
                                        <div class="panel-body"> {{ Bhelper::field('balance','Balansas','eur',false,false) }}
                                            {{ Bhelper::field('rvalue','Pradinis balansas','eur',true,true) }}
                                            {{ Bhelper::field('credit_limit','Kredito limitas','eur',true,true) }}
                                            {{ Bhelper::field('day_credit','Panaudotas dienos kreditas','eur',false,false) }}
                                            {{ Bhelper::field('day_credit_limit','Dienos kredito limitas','eur',true,true) }}
                                            {{ Bhelper::field('credit_allowance','Kredito limito riba','perc',true,true) }}
                                            {{ Bhelper::field('credit','Kredito likutis','eur',false,false) }}
                                            {{ Bhelper::field('countLimit','Operaciju limito likutis','',false,false) }}
                                            {{ Bhelper::field('countLimitDay','Dienos operaciju limitas','',true,true) }}
                                            @include('admin.includes.clients.credit_filter')
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix form-actions">
                                    <div class="col-md-3"><i class="ace-icon fa fa-cog fa-2x fa-spin loadicon green bigger-230 fa-pull-right" style="position:relative; top:6px;"></i></div>
                                    <div class="col-md-9">
                                        <button class="btn btn-info formsave" type="submit"> <i class="ace-icon fa fa-check bigger-110"></i> Išsaugoti </button>
                                        &nbsp; &nbsp; &nbsp;
                                        <button class="btn formcancel" type="button"> <i class="ace-icon fa fa-undo bigger-110"></i> Atšaukti </button>
                                    </div>
                                </div>
                        </form>
                        <div id="all_clients_dialog" class="hide all_clients_dialog">
                            <table id="subgrid_all_clients">
                            </table>
                            <div id="subgrid_all_clients_pager"></div>
                        </div>
                    </div>
                    <div id="clients" class="tab-pane fade"> @include('admin.includes.cards.clients_tab') </div>
                <!--<div id="turnover" class="tab-pane fade"> @include('admin.includes.cards.turnover_tab') </div>-->
                </div>
            </div>
        </div>
    </div>
    <script src="/js/custom/defaults.js"></script>
    <script src="/js/custom/formhelper.js"></script>
    <script type="text/javascript">

        $(function()  {
            conf={
                gridmodel:[
                    {name:'id',index:'id', label:'ID', width:20, sorttype:"int", editable: false, search:false},
                    {name:'number',index:'number', label:'Serijinis nr.', width:100},
                    {name:'type_locale',index:'type', label:'Kortelės tipas', width:100,editable: false, stype:"select",jsonmap:'type.locale',
                        searchoptions:{value:{"":"Visi","discount":"Nuolaidų","pay":"Mokėjimo","payd":"Mokėjimo ir nuolaidų"}}},
                    {name:'balance',index:'balance', label:'Balansas', width:100},
                    {name:'credit_limit',index:'credit_limit', label:'Kredito limitas', width:100},

                    {name:'status_locale',index:'status', label:'Kortelės būklė', width:150,editable: true,edittype:"select",stype:"select", jsonmap:'status.locale',
                        searchoptions:{value:{"":"Visi","tblocked":"Laikinai blokuota","active":"Aktyvi","blocked":"Blokuota","nobonus":"Nutrauktos nuolaidos"}},
                        editoptions:{value:"tblocked:Laikinai blokuota;active:Aktyvi;blocked:Blokuota;nobonus:Nutrauktos nuolaidos"}},
                    {name:'valid_from',index:'valid_from', label:'Galioja nuo',formatter: 'date', formatoptions: { newformat: 'Y-m-d'}, width:100},
                    {name:'valid_to',index:'valid_to', label:'Galioja iki', formatter: 'date', formatoptions: { newformat: 'Y-m-d'}, width:100},
                    {name:'block_reason_locale',index:'block_reason', label:'Kortelė blokuota', width:100, stype:"select",jsonmap:'block_reason.locale',
                        searchoptions:{value:{"":"Visi","nocredit":"Kreditas baigesi","nopay":"Neapmokėta sąstaita","stolen":"Pavogta kortelė"}}},


                    //hidden fields
                    {name:'fcredit', hidden:true},
                    {name:'group_id', hidden:true,jsonmap:'groups.0.id'},
                    {name:'client_id', hidden:true},
                    {name:'pin',index:'pin', hidden:true},
                    {name:'type', hidden:true, jsonmap:'type.value'},
                    {name:'status', jsonmap:'status.value',hidden:true},
                    {name:'block_reason', jsonmap:'block_reason.value', hidden:true},
                    {name:'rvalue', hidden:true},
                    {name:'day_credit', hidden:true},
                    {name:'day_credit_limit', hidden:true},
                    {name:'credit_allowance', hidden:true},
                    {name:'credit', hidden:true},
                    {name:'notes', hidden:true},
                    {name:'countLimit', hidden:true},
                    {name:'countLimitDay', hidden:true},
                ],

                grid_selector : "#grid-table",
                pager_selector : "#grid-pager",
                form_selector : ".tabform",
            };

            all_subtable_selector="#subgrid_all_clients";
            all_subtable_pager="#subgrid_all_clients_pager";
            all_subtable_dialog=".all_clients_dialog";

            $('#clwrap').fadeOut();
            $('#find_client').click(function(){showAllClients()});


            $( document ).on( "makeAddFormEvent", function(e){
                $('#clwrap').fadeIn();
                setFormType('discount');
                $.each(['items','groups'], function(index,value) {
                    $('select[name="credit_'+value+'_filter[]"]').removeAttr('disabled');
                    $('select[name="credit_'+value+'_filter[]"]').val([]).trigger("chosen:updated");
                });
                // Nothind by default.
                $('input[name=credit_filter]').filter(function(){return this.value=='none'}).click();
                $('input[name=credit_filter][value=none]').attr('checked','checked');
                $('input[name=credit_filter][value=groups]').attr('checked',false);
                $('input[name=credit_filter][value=items]').attr('checked',false);
            });
            $( document ).on( "disableFormEvent", function(e){
                $('#clwrap').fadeOut();
                clearClient();
            });

            $(window).on('resize.jqGrid', function () {
                $(all_subtable_selector).jqGrid( 'setGridWidth', $(all_subtable_dialog).width() );
            })

            setClient = function(id,name)
            {
                $("#card_form #client_id").val(id);
                $("#card_form #client_name").removeClass('text-danger').addClass('text-success').html(name);

            }

            clearClient= function()
            {
                $("#card_form #client_id").val('');
                $("#card_form #client_name").removeClass('text-success').addClass('text-danger').html('Klientas nepasirinktas');
            }


            showAllClients = function()
            {
                loadAllClients();
                $( all_subtable_dialog ).removeClass('hide').dialog({
                    resizable: true,
                    width: '90%',
                    title: "<div class='widget-header'><h4 class='smaller'><i class='ace-icon fa fa-user-plus blue'></i> Pasirinkti klientą</h4></div>",
                    title_html: true,
                    modal: true,
                    buttons: [
                        {
                            html: "<i class='ace-icon fa fa-plus bigger-110'></i>&nbsp; Pasirinkti",
                            "class" : "btn btn-success addclientbtn",
                            click: function() {
                                var newclient=$(all_subtable_selector).getGridParam("selrow");
                                console.log(newclient);
                                if(newclient==null)
                                {
                                    //show alert or something
                                }
                                else
                                {
                                    var clientData=$(all_subtable_selector).getRowData(newclient);
                                    console.log(clientData);
                                    setClient(newclient,clientData.name)
                                    $( this ).dialog( "close" );
                                }

                            }
                        }
                        ,
                        {
                            html: "<i class='ace-icon fa fa-times bigger-110'></i>&nbsp; Atšaukti",
                            "class": "btn btn-danger",
                            click: function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    ],
                });
                $(window).triggerHandler('resize.jqGrid');
                return false;
            }
            loadAllClients = function(){
                $(all_subtable_selector).jqGrid('GridUnload');
                $(all_subtable_selector).jqGrid({
                    height: "auto",
                    colModel:[
                        {name:'id',index:'id', label:'ID', width:60, sorttype:"int", editable: false, search:false},
                        {name:'name',index:'name', label:'Pavadinimas', width:400},
                        {name:'type_locale',index:'type', label:'Kortelės tipas', width:400,editable: false, stype:"select",jsonmap:'type.locale',
                            searchoptions:{value:{"":"Visi","discount":"Nuolaidų","pay":"Mokėjimo","payd":"Mokėjimo ir nuolaidų"}}
                        },
                        {name:'credit',index:'credit', label:'Kreditas', width:400},
                        {name:'debit',index:'debit', label:'Debitas', width:400},
                        {name:'status_locale',index:'status', label:'Kortelės būklė', width:600,editable: true,edittype:"select",stype:"select", jsonmap:'status.locale',
                            searchoptions:{value:{"":"Visi","tblocked":"Laikinai blokuota","active":"Aktyvi","blocked":"Blokuota","nobonus":"Nutrauktos nuolaidos"}}
                        },
                        {name:'block_reason_locale',index:'block_reason', label:'Kortelė blokuota', width:400, stype:"select", jsonmap:'block_reason.locale',
                            searchoptions:{value:{"":"Visi","nocredit":"Kreditas baigesi","nopay":"Neapmokėta sąstaita","stolen":"Pavogta kortelė"}}}
                    ],
                    url:opts.api+'clients?rows=20',
                    pager : all_subtable_pager,
                    caption: 'Klientai',
                    ondblClickRow: function(rowid, iRow, iCol, e){
                        $('.addclientbtn').click();
                    }
                });
                $(all_subtable_selector).filterToolbar({ searchOnEnter: false});
                //$(window).triggerHandler('resize.jqGrid');
            }
            beforeSaveForm = function()
            {
                if(!$("#card_form #client_id").val() && !$("#card_form input[name=id]").val())		return false;
                return true;
            }

            hiddenFieldsError = function()
            {
                showSimpleError('Pasirinkite klientą');
            }

            calculateVirtuals = function(data)
            {
                //data['virtual_credit_remain']=Number(data.credit_limit) - Number(data.credit);
            }
            setFormType = function(type)
            {
                console.log('setting form type:'+type);
                if(type=='pay' || type=='payd')
                {
                    $('#card_form #cardlimits').fadeIn(100);
                    $('#card_form .payonly').fadeIn(100);
                    $('#card_form .req').attr('required','required');
                }
                else if(type=='discount')
                {
                    $('#card_form #cardlimits').fadeOut(100);
                    $('#card_form #cardlimits input[type!=radio]').val('');
                    $('#card_form .payonly').fadeOut(100);
                    $('#card_form .req').removeAttr('required');
                }
                checkActive("#card_form");
            };
            additionalOnEnabledForm = function(addable)
            {
                //pay and payd types can be changed
                if(addable) return false;
                var cardtype=$('#card_form select[name=type]').val();
                if(cardtype=='pay' || cardtype=='payd')
                {
                    $('#card_form select[name=type] option[value=discount]').attr('disabled','disabled');
                }
                else
                {//all disabled
                    $('#card_form select[name=type]').attr('disabled','disabled');
                }

                //needed to show all on new card creation
                //setFormType('pay');
            }
            additionalOnDisabledForm = function()
            {
                $('#card_form select[name=type] option').removeAttr('disabled');
            }
            initGridExtra = function()
            {
                $('#card_form select[name=type]').change(function(){
                    setFormType($(this).val());
                });

                $("#card_form select[name=status]").change(function(){checkActive("#card_form")});
            }


            loadCard = function(c){
                $(conf.grid_selector).jqGrid("setGridParam", {
                    postData : {
                        'id':c
                    },
                    search:true,
                    gridComplete:function(){
                        $(conf.grid_selector).jqGrid("setSelection",c);
                        var actuveNumber = $(conf.grid_selector).jqGrid('getCell',c,'number');
                        $("#gs_number").val(actuveNumber);
                        $(conf.grid_selector).jqGrid("setGridParam", {
                            gridComplete: function () {},
                        })
                    }
                }).trigger("reloadGrid");

            }
            checkId = function()
            {
                var url_string = window.location.href;
                var url = new URL(url_string);
                var c = url.searchParams.get("id");
                if(c)
                {
                    console.log('running load');
                    setTimeout(function(){loadCard(c)},1000);
                }
            }
            initGrid();
            clearForm();
            disableForm();
            initButtons();
            checkId();
            preprocessDataExtra = function(value) {
                if (value.fcredit) {
                    value.fcredit = JSON.stringify(value.fcredit);
                }
            }

        });

        $("input[name='countLimitDay']").change(function(){
            $countLimitDay = $(this).val();
            $("input[name*='countLimit']").val($countLimitDay);
        });
    </script>
@stop