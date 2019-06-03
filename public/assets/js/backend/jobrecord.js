define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'jobrecord/index' + location.search,
                    add_url: 'jobrecord/add',
                    edit_url: 'jobrecord/edit',
                    del_url: 'jobrecord/del',
                    multi_url: 'jobrecord/multi',
                    table: 'job_record',
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
                        {field: 'job_id', title: __('Job_id')},
                        {field: 'user.nickname', title: __('User.nickname')},
                        {field: 'user.avatar', title: __('User.avatar'),events:Table.api.events.images,formatter:Table.api.formatter.images},


                        {field: 'job.entry', title: __('Job.entry'), operate:'BETWEEN'},
                        {field: 'job.recommend', title: __('Job.recommend'), operate:'BETWEEN'},
                        {field: 'job.commission', title: __('Job.commission'), operate:'BETWEEN'},

                        {field: 'state', title: __('State'), searchList: {"wait":__('State wait'),"pass":__('State pass'),"refuse":__('State refuse')}, formatter: Table.api.formatter.normal},
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