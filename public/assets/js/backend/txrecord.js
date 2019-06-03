define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'txrecord/index' + location.search,
                    add_url: 'txrecord/add',
                    //edit_url: 'txrecord/edit',
                    del_url: 'txrecord/del',
                    multi_url: 'txrecord/multi',
                    table: 'txrecord',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user.nickname', title: __('User.nickname')},
                        {field: 'user.avatar', title: __('User.avatar'),events:Table.api.events.image,formatter:Table.api.formatter.image},
                        {field: 'user.name', title: __('User.name')},
                        {field: 'user.tel', title: __('User.tel')},

                        {field: 'tx_cost', title: __('Tx_cost'), operate:'BETWEEN'},
                        {field: 'sj_cost', title: __('Sj_cost'), operate:'BETWEEN'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'state', title: __('State'), searchList: {"wait":__('State wait'),"pass":__('State pass'),"refuse":__('State refuse')}, formatter: Table.api.formatter.normal},

                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,buttons:[
                            {
                                name:'pass',
                                text:'通过',
                                title:'通过',
                                icon:'fa fa-check',
                                classname:'btn btn-xs btn-primary btn-success btn-ajax',
                                url:'txrecord/pass?id={row.id}&user_id={row.user_id}&tx_cost={row.tx_cost}',
                                success:function(e){
                                    $('.btn-refresh').trigger('click');
                                },
                                visible:function(row){
                                    return row.state=='wait' ? true : false;
                                }
                            },
                            {
                                name:'refuse',
                                text:'拒绝',
                                title:'拒绝',
                                icon:'fa fa-warning',
                                classname:'btn btn-xs btn-primary btn-warning btn-ajax',
                                url:'txrecord/refuse?id={row.id}&user_id={row.user_id}&tx_cost={row.tx_cost}',
                                success:function(e){
                                    $('.btn-refresh').trigger('click');
                                },
                                visible:function(row){
                                    return row.state=='wait' ? true : false;
                                }
                            }
                        ]}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },

        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});