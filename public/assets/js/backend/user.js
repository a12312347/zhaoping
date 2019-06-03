define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/index' + location.search,
                    add_url: 'user/add',
                    edit_url: 'user/edit',
                    del_url: 'user/del',
                    multi_url: 'user/multi',
                    table: 'user',
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
                        {field: 'name', title: __('Name')},
                        {field: 'nickname', title: __('Nickname')},
                        {field: 'avatar', title: __('Avatar'),events:Table.api.events.image,formatter:Table.api.formatter.image},
                        {field: 'openid', title: __('Openid')},
                        {field: 'wechat', title: __('Wechat')},
                        {field: 'tel', title: __('Tel')},
                        {field:'balance',title:__('Balance')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange'},
                        {field: 'status', title: __('Status'), searchList: {"normal":__('Status normal'),"hidden":__('Status hidden')}, formatter: Table.api.formatter.status},
                        {field:'is_ban',title:__('Is_ban'),searchList:{'1':__('Is_ban 1'),'0':__('Is_ban 0')},formatter:Table.api.formatter.toggle},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,buttons:[
                            {
                                name:'agent',
                                classname:'btn btn-xs btn-primary btn-ajax btn-success',
                                icon:'fa fa-asterisk',
                                text:'',
                                title:'设置为代理商',
                                url:'user/agent?id={row.id}',
                                success:function(e){
                                    $('.btn-refresh').trigger('click');
                                },
                                visible:function(row){
                                    return row.user_agent==1?false:true;
                                }
                            },
                            {
                                name:'delagent',
                                classname:'btn btn-xs btn-primary btn-ajax btn-warning',
                                icon:'fa fa-times-circle',
                                text:'',
                                title:'取消代理商',
                                url:'user/delagent?id={row.id}',
                                success:function(e){
                                    $('.btn-refresh').trigger('click');
                                },
                                visible:function(row){
                                    return row.user_agent==0?false:true;
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