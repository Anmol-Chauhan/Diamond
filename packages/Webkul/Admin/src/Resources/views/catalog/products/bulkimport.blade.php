@extends('admin::layouts.content')

@section('page_title')
    {{ __('Bulk Product Import') }}
@stop

@section('css')
    <style>
        .table td .label {
            margin-right: 10px;
        }
        .table td .label:last-child {
            margin-right: 0;
        }
        .table td .label .icon {
            vertical-align: middle;
            cursor: pointer;
        }
    </style>
@stop

@section('content')
    <div class="content">
		<form class="form-horizontal" action="{{ route('admin.catalog.products.importProducts') }}" method="post" name="upload_excel" enctype="multipart/form-data">
            <div class="page-header">
                <div class="page-title">
                    <h1>
                        <i class="icon angle-left-icon back-link" onclick="window.location = '{{ route('admin.catalog.products.index') }}'"></i>
                        {{ __('Bulk Product Import') }}
                    </h1>
                    <p>Select the product type and upload csv to import products.</p> 
                </div>
                <!-- <div class="page-action">
                    <button type="submit" class="btn btn-lg btn-primary">
                        {{ __('admin::app.catalog.products.save-btn-title') }}
                    </button>
                </div> -->
            </div>
            <div class="page-content">
    			 @csrf()
                 <accordian :title="'{{ __('admin::app.catalog.products.general') }}'" :active="true">
                    <div slot="body">
                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.before') !!}
                        <div class="control-group">
                            <label for="type" class="required">{{ __('Product Type') }}</label>
                            <select class="control" id="product_type" name="product_type" required="'required'">
                                <option value="">Select</option>
                                <option value="13">Bracelets</option>
                                <option value="14">Fashion Rings</option>
                                <option value="15">Promise Rings</option>
                                <option value="16">Earrings</option>
                                <option value="17">Wedding Bands</option>
                                <option value="18">Necklaces</option>
                                <option value="19">Engagement Rings</option>
                            </select>
                        </div>
                        <div class="control-group">
                            <label for="file" class="required">{{ __('Select File') }}</label>
                            <input type="file" name="file" id="file" accept=".csv" class="control" style="padding-top: 4px;">
                        </div>
                        <div class="page-action control-group">
                            <button type="submit" class="btn btn-lg btn-primary" id="submit" name="Import" data-loading-text="Loading...">
                                {{ __('Import') }}
                            </button>
                        </div>
                        {!! view_render_event('bagisto.admin.catalog.product.create_form_accordian.general.controls.after') !!}
                    </div>
                </accordian>
            </div>
        </form>
    </div>
@stop
