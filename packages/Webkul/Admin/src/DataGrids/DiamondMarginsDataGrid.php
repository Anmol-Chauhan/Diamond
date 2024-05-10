<?php

namespace Webkul\Admin\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\Ui\DataGrid\DataGrid;

class DiamondMarginsDataGrid extends DataGrid
{
    protected $index = 'id';

    protected $sortOrder = 'ASC';

    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('custom_kdmvendors')->select('id')->addSelect('id', 'name', 'vendor_code', 'abbreviation', 'type', 'shipdays', 'status');

        $this->setQueryBuilder($queryBuilder);
    }

    public function addColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('admin::app.id'),
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.name'),
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
            'filterable' => true
        ]);

        
    }

    public function prepareActions()
    {
        $this->addAction([
            'title'  => trans('admin::app.datagrid.edit'),
            'method' => 'GET',
            'route'  => 'admin.catalog.margins.index',
            'icon'   => 'icon pencil-lg-icon',
            'manageMargin'   => 'Yes',
        ]);

        
    }
}