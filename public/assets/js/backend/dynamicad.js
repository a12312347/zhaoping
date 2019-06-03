define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'dynamicad/index' + location.search,
                    add_url: 'dynamicad/add',
                    edit_url: 'dynamicad/edit',
                    del_url: 'dynamicad/del',
                    multi_url: 'dynamicad/multi',
                    dymamicinfo:'dynamicad/dynamicinfo',
                    table: 'dynamic_ad',
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
                        {field: 'dynamic_id', title: __('Dynamic_id')},
                        {field: 'dynamic.title', title: __('Dynamic.title')},
                        {field: 'image', title: __('Image'),events:Table.api.events.image,formatter:Table.api.formatter.image},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,buttons:[
                            {
                                name:'dynamicinfo',
                                text:'',
                                title:'查看动态',
                                icon:'fa fa-th-list',
                                classname:'btn btn-xs btn-primary btn-dialog',
                                url:'dynamicad/dynamicinfo?id={row.id}'
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
        dynamicinfo: function () {
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