@extends('admin::layouts.content')
@inject ('vendormargin', 'Webkul\Product\Helpers\VendormarginHelper')

@section('page_title')
    {{ __('admin::app.catalog.margins.title') }}
@stop

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>{{ __('admin::app.catalog.margins.title') }}</h1>
            </div>

            
        </div>

        {!! view_render_event('bagisto.admin.catalog.families.list.before') !!}

        <div class="page-content">
            {!! app('Webkul\Admin\DataGrids\DiamondMarginsDataGrid')->render() !!}

        </div>

       
        
    </div>
	@php
		$vendorMarginData = $vendormargin->getVendorMarginsDetails();
		//print_r($vendorMarginData);
	@endphp
	@foreach($vendorMarginData as $vendorMargin)
		<input type="hidden" name="type_name_{{$vendorMargin->vendor_id}}" id="{{$vendorMargin->vendor_code}}" value="{{$vendorMargin->vendor_id}}">
		
		<input type="hidden" name="name_{{$vendorMargin->vendor_id}}" id="name_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->vendor_name}}">
		
		<input type="hidden" name="vendor_id_{{$vendorMargin->vendor_id}}" id="vendor_id_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->vendor_id}}">
		
		<input type="hidden" name="vendor_code_{{$vendorMargin->vendor_id}}" id="vendor_code_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->vendor_code}}">
		
		<input type="hidden" name="shipdays_{{$vendorMargin->vendor_id}}" id="shipdays_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->shipdays}}">
		
		<input type="hidden" name="type_{{$vendorMargin->vendor_id}}" id="type_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->type}}">
		
		<input type="hidden" name="margin_1_500_{{$vendorMargin->vendor_id}}" id="margin_1_500_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_1_500}}">
		
		<input type="hidden" name="margin_501_1000_{{$vendorMargin->vendor_id}}" id="margin_501_1000_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_501_1000}}">
		
		<input type="hidden" name="margin_1001_2000_{{$vendorMargin->vendor_id}}" id="margin_1001_2000_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_1001_2000}}">
		
		<input type="hidden" name="margin_2001_3500_{{$vendorMargin->vendor_id}}" id="margin_2001_3500_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_2001_3500}}">
		
		<input type="hidden" name="margin_3501_5000_{{$vendorMargin->vendor_id}}" id="margin_3501_5000_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_3501_5000}}">
		
		<input type="hidden" name="margin_5001_10000_{{$vendorMargin->vendor_id}}" id="margin_5001_10000_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_5001_10000}}">
		
		<input type="hidden" name="margin_10001_15000_{{$vendorMargin->vendor_id}}" id="margin_10001_15000_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_10001_15000}}">
		
		<input type="hidden" name="margin_15001_20000_{{$vendorMargin->vendor_id}}" id="margin_15001_20000_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_15001_20000}}">
		
		<input type="hidden" name="margin_20001_25000_{{$vendorMargin->vendor_id}}" id="margin_20001_25000_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_20001_25000}}">
		
		<input type="hidden" name="margin_25001_30000_{{$vendorMargin->vendor_id}}" id="margin_25001_30000_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_25001_30000}}">
		
		<input type="hidden" name="margin_30001_50000_{{$vendorMargin->vendor_id}}" id="margin_30001_50000_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_30001_50000}}">
		
		<input type="hidden" name="margin_50001_100000_{{$vendorMargin->vendor_id}}" id="margin_50001_100000_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_50001_100000}}">
		
		<input type="hidden" name="margin_100001_1000000_{{$vendorMargin->vendor_id}}" id="margin_100001_1000000_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_100001_1000000}}">
		
		<input type="hidden" name="margin_1000001_2000001_{{$vendorMargin->vendor_id}}" id="margin_1000001_2000000_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->margin_1000001_2000000}}">
		
		<input type="hidden" name="updated_at_{{$vendorMargin->vendor_id}}" id="updated_at_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->updated_at}}">
		
		<input type="hidden" name="margin_id_{{$vendorMargin->vendor_id}}" id="margin_id_{{$vendorMargin->vendor_id}}" value="{{$vendorMargin->id}}">
		
	@endforeach
		
<!--Vendor Detail Section -->
			<div class="postbox-container vendorallcontainer" id="vendordetail-container">
			<form method="POST" action="{{ route('admin.catalog.margins.update') }}" @submit.prevent="onSubmit" enctype="multipart/form-data">
				<div class="topbar-heading">
					<h2>Vendor Details</h2>
					<div class="default-values"><a class="default-vendors" rel="" id="setDefault">Set Default Margin Values</a></div>
				</div>
				<div class="vendor-detail-manage vendor-inner">
					<div class="vendor-description">
						<ul>
							<li class="vendorname"><span class="vname one" id="vendorNameChar">R</span><span id="vendorName"></span></li>
							<li>Vendor Type : <span id="vendorType"></span></li>
							<li>Vendor Code : <span id="vendorCode"></span></li>
							<li>Last Update : <span id="LastUpdate"></span></li>
							<li>Ship Days : <span id="Ship_days"></span></li>
						</ul>
					</div>
					<div class="vendor-manage-fields">
					<table class="customers wp-list-table widefat fixed table vendor-detail-table" cellspacing="0">
						<thead>
							<th>From</th>
							<th>To</th>
							<th>% Value</th>
						</thead>
						<tbody>
							
								<tr>
									<td><input type="number" name="from" value="1" disabled></td>
									<td><input type="number" name="to" value="500" disabled></td>
									<td><input type="number" name="margin_1_500" id="margin_1_500" value=""></td>
								</tr>
								<tr>
									<td><input type="number" name="from" value="501" disabled></td>
									<td><input type="number" name="to" value="1000" disabled></td>
									<td><input type="number" name="margin_501_1000" id="margin_501_1000" value=""></td>
								</tr>
								<tr>
									<td><input type="number" name="from" value="1001" disabled></td>
									<td><input type="number" name="to" value="2000" disabled></td>
									<td><input type="number" name="margin_1001_2000" id="margin_1001_2000" value=""></td>
								</tr>
								<tr>
									<td><input type="number" name="from" value="2001" disabled></td>
									<td><input type="number" name="to" value="3500" disabled></td>
									<td><input type="number" name="margin_2001_3500" id="margin_2001_3500" value=""></td>
								</tr>
								<tr>
									<td><input type="number" name="from" value="3501" disabled></td>
									<td><input type="number" name="to" value="5000" disabled></td>
									<td><input type="number" name="margin_3501_5000" id="margin_3501_5000" value=""></td>
								</tr>
								<tr>
									<td><input type="number" name="from" value="5001" disabled></td>
									<td><input type="number" name="to" value="10000" disabled></td>
									<td><input type="number" name="margin_5001_10000" id="margin_5001_10000" value=""></td>
								</tr>
								<tr>
									<td><input type="number" name="from" value="10001" disabled></td>
									<td><input type="number" name="to" value="15000" disabled></td>
									<td><input type="number" name="margin_10001_15000" id="margin_10001_15000" value=""></td>
								</tr>
								<tr>
									<td><input type="number" name="from" value="15001" disabled></td>
									<td><input type="number" name="to" value="20000" disabled></td>
									<td><input type="number" name="margin_15001_20000" id="margin_15001_20000" value=""></td>
								</tr>
								<tr>
									<td><input type="number" name="from" value="20001" disabled></td>
									<td><input type="number" name="to" value="25000" disabled></td>
									<td><input type="number" name="margin_20001_25000" id="margin_20001_25000" value=""></td>
								</tr>
								<tr>
									<td><input type="number" name="from" value="25001" disabled></td>
									<td><input type="number" name="to" value="30000" disabled></td>
									<td><input type="number" name="margin_25001_30000" id="margin_25001_30000" value=""></td>
								</tr>
								<tr>
									<td><input type="number" name="from" value="30001" disabled></td>
									<td><input type="number" name="to" value="50000" disabled></td>
									<td><input type="number" name="margin_30001_50000" id="margin_30001_50000" value=""></td>
								</tr>
								<tr>
									<td><input type="number" name="from" value="50001" disabled></td>
									<td><input type="number" name="to" value="100000" disabled></td>
									<td><input type="number" name="margin_50001_100000" id="margin_50001_100000" value=""></td>
								</tr>
								<tr>
									<td><input type="number" name="from" value="100001" disabled></td>
									<td><input type="number" name="to" value="1000000" disabled></td>
									<td><input type="number" name="margin_100001_1000000" id="margin_100001_1000000" value=""></td>
								</tr>
								<tr>
									<td><input type="number" name="from" value="1000001" disabled></td>
									<td><input type="number" name="to" value="2000000" disabled></td>
									<td><input type="number" name="margin_1000001_2000000" id="margin_1000001_2000000" value=""></td>
								</tr>
							
						</tbody>
					</table>
					@csrf()
                    <input type="hidden" name="locale" value="all"/>
					<input type="hidden" value="" id="margin_id" name="margin_id">
					<input type="hidden" value="" id="vendor_id" name="vendor_id">
						<div class="save-manage-values">
							<button class="save-values btnv" type="submit">Save</button>
							<span class="cancel-values btnv">Cancel</span>
						</div>
				
					</div>
				</div>
				</form>
			</div>
			<!--Vendor List Section -->
@stop
<style>
.table table {
	border-collapse: collapse;
    text-align: left;
    width: 40% !important;
}
</style>

    <script>
        {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
		var base_path = {!! json_encode(url('/')) !!};
    </script>

    <script src="{{ asset('themes/bliss/assets/js/jquery.js')}}"></script>
    <script src="{{ asset('themes/bliss/assets/js/bootstrap.bundle.js')}}"></script>
    <script src="http://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="http://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="http://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script type="text/javascript" src="{{ asset('themes/bliss/assets/js/jquery-ui.js')}}"></script>
    <script type="text/javascript" src="{{ asset('themes/bliss/assets/js/jquery.ui.touch-punch.min.js')}}"></script>

<script>
	$( document ).delegate( ".manage-btn", "click", function() {
		var vendorId = $(this).attr('rel');
		var vendor_type = $('#type_'+vendorId).val();
		var vendorName = $('#name_'+vendorId).val();
	  jQuery('.manage-btn').removeClass('showdetail');
	  jQuery(this).addClass('showdetail');
	  jQuery(this).parents('tr').addClass('active').siblings('tr').removeClass('active');
	  jQuery('#vendordetail-container').show();
	  $('#vendorNameChar').html(vendorName.charAt(0));
	  $('#vendorName').html($('#name_'+vendorId).val());
	  $('#vendorCode').html($('#vendor_code_'+vendorId).val());
	  $('#Ship_days').html($('#shipdays_'+vendorId).val());
	  $('#vendorType').html($('#type_'+vendorId).val());
	  $('#LastUpdate').html($('#updated_at_'+vendorId).val());
	  $('#margin_id').val($('#margin_id_'+vendorId).val());
	  $('#vendor_id').val(vendorId);
	  $('#setDefault').attr('rel',$('#DEFAULT'+vendor_type).val());
	  updateMarginslap(vendorId);
	  
	});	
	$( document ).delegate( "#setDefault", "click", function() {
		var vendorId = $(this).attr('rel');
		
	  updateMarginslap(vendorId);
	  
	});	
	function updateMarginslap(vendorId){
	  $('#margin_1_500').val($('#margin_1_500_'+vendorId).val());
	  $('#margin_501_1000').val($('#margin_501_1000_'+vendorId).val());
	  $('#margin_1001_2000').val($('#margin_1001_2000_'+vendorId).val());
	  $('#margin_2001_3500').val($('#margin_2001_3500_'+vendorId).val());
	  $('#margin_3501_5000').val($('#margin_3501_5000_'+vendorId).val());
	  $('#margin_5001_10000').val($('#margin_5001_10000_'+vendorId).val());
	  $('#margin_10001_15000').val($('#margin_10001_15000_'+vendorId).val());
	  $('#margin_15001_20000').val($('#margin_15001_20000_'+vendorId).val());
	  $('#margin_20001_25000').val($('#margin_20001_25000_'+vendorId).val());
	  $('#margin_25001_30000').val($('#margin_25001_30000_'+vendorId).val());
	  $('#margin_30001_50000').val($('#margin_30001_50000_'+vendorId).val());
	  $('#margin_50001_100000').val($('#margin_50001_100000_'+vendorId).val());
	  $('#margin_100001_1000000').val($('#margin_100001_1000000_'+vendorId).val());
	  $('#margin_1000001_2000000').val($('#margin_1000001_2000000_'+vendorId).val());
	}
	
	$( document ).delegate( ".cancel-values", "click", function() {
		jQuery('#vendordetail-container').hide();
		jQuery('.manage-btn').removeClass('showdetail');
	});	
	</script>