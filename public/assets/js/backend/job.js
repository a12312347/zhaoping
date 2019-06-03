define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'job/index' + location.search,
                    add_url: 'job/add',
                    edit_url: 'job/edit',
                    del_url: 'job/del',
                    multi_url: 'job/multi',
                    table: 'job',
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
                        {field:'company_images',title:__('Company_images'),events:Table.api.events.images,formatter:Table.api.formatter.images},
                        {field:'interview_images',title:__('Interview_images'),events:Table.api.events.images,formatter:Table.api.formatter.images},
                        {field:'dormitory_images',title:__('Dormitory_images'),events:Table.api.events.images,formatter:Table.api.formatter.images},
                        {field:'company_images',title:__('Company_images'),events:Table.api.events.images,formatter:Table.api.formatter.images},
                        {field:'canteen_images',title:__('Canteen_images'),events:Table.api.events.images,formatter:Table.api.formatter.images},
                        {field:'staff_images',title:__('Staff_images'),events:Table.api.events.images,formatter:Table.api.formatter.images},
                        {field:'company',title:__('Company')},
                        {field: 'entry', title: __('Entry'), operate:'BETWEEN'},
                        {field: 'recommend', title: __('Recommend'), operate:'BETWEEN'},
                        {field: 'commission', title: __('Commission'), operate:'BETWEEN'},
                        {field: 'nature', title: __('Nature')},
                        {field: 'type', title: __('Type')},
                        {field: 'address', title: __('Address')},
                        {field: 'state', title: __('State'), searchList: {"1":__('State 1'),"2":__('State 2')}, formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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

//$("[data-toggle='addresspicker']").on('click',function(res){
//    console.log(res);
//});
$("[data-toggle='addresspicker']").data('callback',function(res){
    $("#c-address").val(res.address);
    $("#c-location").val(res.lat+','+res.lng);
});

//$('#c-address').on('click', "[data-toggle='addresspicker']", function () {
//
//};