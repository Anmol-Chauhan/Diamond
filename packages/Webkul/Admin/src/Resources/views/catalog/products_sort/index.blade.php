@extends('admin::layouts.content')
@inject ('productImageHelper', 'Webkul\Product\ProductImage')
@section('page_title')
{{ __('admin::app.catalog.products.title') }}
@stop

@section('content')

<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h1>Product Sort</h1>
        </div>
    </div>

    <!--Filter Section-->
    @if(!empty($categoryArr ) )
    <form method="get" action="{{ route('admin.catalog.sorting') }}" id="category-form">
        <div class="category-div">
            <div class="control-group">
                <div>
                    <label for="category" >Parent Categories</label>
                    <select class="control" id="p_category"  name="p_category" onchange="get_category(this.value);" required>
                        @foreach($categoryArr as $val)
                        @if(!empty($p_category)&& $val==$p_category)
                            <option value="{{$val}}" selected>{{ucwords(str_replace('-',' ',$val))}}</option>
                        @else
                            <option value="{{$val}}" >{{ucwords(str_replace('-',' ',$val))}}</option>
                        @endif
                        @endforeach      
                    </select>
                </div>

                <div class=" category">
                    <label for="sub_category" >Categories</label>
                    <select class="control" id="category" name="category" required>
                        <option value="">Select</option>
						@foreach($dataCategory as $data)
                        @if(!empty($categoryId) && $data->category_id == $categoryId)
                            <option value="{{$data->category_id}}" selected > {{$data->name}}</option>					
                        @else
                            <option value="{{$data->category_id}}" > {{$data->name}}</option>
                        @endif
						@endforeach
                    </select>
                </div>
            </div>            
        </div>
    </form>

   
    @endif
    <!--Filter End-->

    <!--Product Section-->
	@php $maxProd=0; @endphp
    @if(!empty($products))
    <section class="Product-sort">
        <div class="container ">
         @if(count($products)>0)
            <form method="post" action="{{ route('admin.catalog.sorting.update') }}" id="form" >
                @csrf
                <div class="submit-div">
                    <button type="submit" class="btn btn-primary submit_form">Submit</button>
                </div>
                
                <div id="sortable" class="list-unstyled row">
                    @php $maxProd=$products->total(); @endphp
					@foreach($products as $key=>$value)
					@php
                        $GalleryImages = $productImageHelper->getListGalleryImages($value);
					    $defaultImg=$value->image;
						if(!$defaultImg){
						  $defaultImg = reset($GalleryImages[array_key_first($GalleryImages)]);
						}
					@endphp
                    @if($value->min_price > 0)
                    <div class="col-lg-3">
                        <div class="item">
                            @if($value->product_order!='5555' && $value->product_order!='' && $value->product_order!=0)
                            <div class="rank">{{$value->product_order}}</div>
                            @else
                            <div class="rank">{{$key+1}}</div>
                            @endif
                            <input type="hidden" value="{{$value->product_id}}" name="sort_value[]">
                            <img src="{{ asset('themes/bliss/assets/'.$defaultImg)}}" alt="" width="100%" onerror="this.src='{{ asset('vendor/webkul/ui/assets/images/product/meduim-product-placeholder.png') }}'">
                            <h6>{{$value->name}}</h6>
                            <p>{{strtoupper($value->sku)}}</p>
                            <h5>${{number_format($value->min_price)}}</h5>
                            <button type="button" class="btn btn-assign">Assign Rank</button>
                            <div class="input-container">
                                <span class="minus">-</span>
                                <input id="rank-input-{{$value->product_id}}" name="rank[]" class="rank_assign" type="number" value="0" max={{$maxProd}} />
                                <span class="plus">+</span>
                                <button onclick="rank_assign_validate('{{$value->product_id}}');" type="button" class="btn-ok">&#x2713;</button>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
					
					@if($maxProd <= 24)
					<div class="col-lg-3">
						<div class="item-1"></div>
					</div>
					<div class="col-lg-3">
						<div class="item-1"></div>
					</div>
					<div class="col-lg-3">
						<div class="item-1"></div>
					</div>
					@endif
                </div>
            </form>
		  @else
		  <h3>No Products Found</h3>
		  @endif
		  
		  <center><div class="loader d-none" ></div></center>
		  
        </div>
    </section>
    <!--<div class="d-flex">
         {!! $products->links() !!}
    </div>-->
    @endif
    <!--Product Section End-->
</div>

@php
    $finalValue=0;
    if(!empty($products)){
		$pages = explode(',', core()->getConfigData('catalog.products.storefront.products_per_page'));
		$perPage = !empty(current($pages)) ? current($pages) : 60;
		$gettotalval = $products->total()/$perPage;
		$getroundval = round($products->total()/$perPage);
		
		if($gettotalval==$getroundval)
		$finalValue = $gettotalval;
		elseif($gettotalval > $getroundval)
		$finalValue = $getroundval+1;
		else
		$finalValue = $getroundval;
	}
@endphp
<!--Hidden Fields-->
<input type="hidden" name="page_number" id="page_number_id" value="1" />
<input type="hidden" name="page_obj_start" id="page_obj_start" value="1"/>
<input type="hidden" name="page_obj_end" id="page_obj_end" value="1"/>
<input type="hidden" name="total_page_count" id="total_page_count_id" value="{{ $finalValue }}" />
@if(!empty($categoryId)) 
<input type="hidden" id="ring_category_id" name="ring_category_id" value="{{$categoryId}}">
@endif
<!--Hidden Fields End-->

@stop

@push('scripts')

<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>

<script>
$(document).ready(function(){
	window.base_url = {!! json_encode(url('/')) !!};
})
</script>

<script>
$(function(e){
    $(window).scroll(function (e) {
		e.preventDefault();
        var $div = $("#sortable");
        var divPlacement = parseInt($div.offset().top + parseInt($div.height()));
        var screenBottom = $(this).scrollTop() + parseInt($(window).height());
        divPlacement -= 1500;
        if(screenBottom >= divPlacement) {
		    var startpage = parseInt($("input[name=page_obj_start]").val());
			var endpage = parseInt($("input[name=page_obj_end]").val());
			var page = parseInt($("input[name=page_number]").val());
			var finalpage = parseInt($("input[name=total_page_count]").val());
			
			if (startpage == endpage && page < finalpage)
			{
				loadringajax(divPlacement);
				var obstart = startpage + 1;
  				jQuery("#page_obj_start").val(obstart);
			}
			
        }
 
    });
});

function loadringajax(scrollBottom){
  var pagenum = parseInt($('input[name=page_number]').val()) + 1;
  jQuery("#page_number_id").val(pagenum);
  var ringCategoryId = jQuery("#ring_category_id").val();
  var total_page_count = jQuery("#total_page_count_id").val();
  var ajaxurl = base_url+'/search-rings-sorting/'+ringCategoryId;
  $('.loader').removeClass('d-none');
  jQuery.get(
      ajaxurl,
      {
        'action': 'rings_filter_ajs',
		'page': pagenum,
		'total_page': total_page_count,
        'filter': 'rings_filter'
      }, 
      function(response){
        jQuery("#sortable").append(response);
		$('.loader').addClass('d-none');
		var incpagenum = parseInt($('input[name=page_obj_end]').val()) + 1;
  		jQuery("#page_obj_end").val(incpagenum);
      }
    );
}
</script>

 <script>
  function rank_assign_validate(val){
    var rank = $('#rank-input-'+val).val();
    var max_allow = <?php echo $maxProd; ?>;
    if(rank!=0){
		if(rank > max_allow){
        alert('Rank should not be more than '+max_allow);
		}else{
		$('#form').submit();	
		}
    }else{
		alert('Rank should be more than 0');
	}
  }
</script>

<script>
    $(document).ready(function() {
        $('#category').change(function() {
            $('#category-form').submit();
        });
    });

    $(function() {
        $("#sortable").sortable({
            change: function(event, ui) {
                $('.rank_assign').val('0');
                $(".submit_form").attr("disabled", false);
            },
            revert: true
        });
        $("#draggable").draggable({
            connectToSortable: "#sortable",
            helper: "clone",
            revert: "invalid"
        });
        $("ul, li").disableSelection();
    });
</script>



<script>
    $(document).ready(function() {
        $('.minus').click(function() {
            var $input = $(this).parent().find('input');
            var count = parseInt($input.val()) - 1;
            count = count < 1 ? 0 : count;
            $input.val(count);
            $input.change();
            return false;
        });
        $('.plus').click(function() {
            var $input = $(this).parent().find('input');
            $input.val(parseInt($input.val()) + 1);
            $input.change();
            return false;
        });
    });
</script>


<script>
    $(function(){
		$(document).on("click", '.btn-assign',function(){
			$('.rank_assign').val('0');
			$(".submit_form").attr("disabled", true);
			var inputContainer = this.nextElementSibling;
			var isVisible = inputContainer.classList.contains('visible');

			// Hide all input containers first
			var allInputContainers = document.querySelectorAll('.input-container');
			allInputContainers.forEach(function(container) {
				container.classList.remove('visible');
			});

			// Show the corresponding input container if it's not already visible
			if (!isVisible) {
				inputContainer.classList.add('visible');
			}
		});                                
    });
</script>

<script>
    function reloadPage(getVar, getVal) {
        let url = new URL(window.location.href);
        url.searchParams.set(getVar, getVal);

        window.location.href = url.href;
    }
</script>

<script>
    function get_category(val) {
        var formData = {
            "_token": "{{ csrf_token() }}",
            'valueCat': val
        };
        $.ajax({
            type: 'POST',
            url: 'sorting/getCategory',
            data: formData,
            success: function(data) {
                $('.category').removeClass('d-none');
                $("#category").html(data);
            }
        });
    }
</script>

@endpush


<style>
    .category-div {
        display: flex;
        align-items: center;
    }

    .category-div .control-group {
        width: 55%;
        display: flex;
    }

    .category-div .control-group .control {
        width: 85%;
    }

 

    .category-div .control-group div {
        width: 100%;
    }

    /* rank */
    .Product-sort .row .item .rank {
        position: absolute;
        top: 25px;
        left: 25px;
        width: 32px;
        height: 32px;
        border-radius: 50vh;
        background-color: #545454;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    ol,
    ul {
        padding-left: 0rem !important;
    }

    .Product-sort .row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }

    .Product-sort .row>div {
        display: inline-block;
        flex: 1 1 25%;
        align-items: center;
        flex-direction: column;
        padding: 10px;
    }

    .Product-sort .row .item {
        min-height: 370px;
        width: auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: auto;
        /* border: 1px solid #59695e; */
        border-radius: 5px;
        position: relative;
    }

    .ui-sortable-helper .item {
        border: 1px solid #59695e;
        border-left: 10px solid #59695e !important;
        transition: 0.3s;
        border-radius: 15px !important;
        background-color: #fff;
    }

    .Product-sort .row .item-1 {
        width: auto;
    }

    .Product-sort .row .item h6 {
        color: #545454;
        font-size: 13px;
        font-weight: 400;
        line-height: normal;
        margin: 0;
        text-align: center;
        width: 96%;
        height: 50px;
        /* background-color: rebeccapurple; */

    }

    .Product-sort .row .item p {
        color: #333;
        font-size: 15px;
        font-weight: 400;
        margin: 0;
        text-align: center;
        width: 95%;
    }

    .Product-sort .row .item h5 {
        font-size: 18px;
        font-weight: 400;
        color: #59695e;
        line-height: 1.22;
        margin: 10px 0 15px 0;
    }

    .Product-sort .item img {
        width: 200px;
    }


    .input-container {
        overflow: hidden;
        max-height: 0;
        opacity: 0;
        transition: max-height 0.5s ease-in, opacity 0.5s ease-in;
        margin-bottom: 10px;
        /* position: absolute; */
        display: flex;
        align-items: center;
        justify-content: center;
        width: 80%;

    }

    .input-container span {
        font-size: 28px;
        margin-left: 10px;
        margin-right: 10px;
        color: rgb(204, 204, 204);
        cursor: pointer;
    }

    .input-container span:hover {
        color: #333;
    }

    .input-container input {
        width: 57px;
        padding: 7px;
        padding-left: 12px;
        border: 1px solid gray;
        border-radius: 5px;
        text-align: center;
        border: none;
        font-size: 20px;
        color: #333;
        /* margin: 7px 0; */
    }

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .input-container .btn-ok {
        border: none;
        padding: 8px 12px;
        background-color: #59695e;
        border-radius: 3px;
        color: #ffffff;
        font-weight: 700;
        cursor: pointer;
    }

    .visible {
        max-height: 100px;
        opacity: 1;
    }

    .Product-sort .btn-assign {
        width: 50%;
        background-color: #59695e81;
        margin-bottom: 10px;
        font-weight: 500;
        border-radius: 4px !important;
        padding: 8px !important;
        box-shadow: none !important;
        color: #333;
        font-weight: 600;
        transition: 0.3s;
    }

    .Product-sort .btn-assign:hover {
        background-color: #59695e76;
        color: black;
    }

    .category-div button {
        margin-right: 10px;
        padding: 10px 20px;
        height: 35px;
    }

    .submit-div {
        width: 100%;
        display: flex;
        justify-content: end;
        position: absolute;
        top: 63px;
        right: 60px;
        margin-top: -55px;
        z-index: 4;
        position: sticky;
    }

    .submit-div button {
        margin-top: 20px;
        margin-right: 10px;
        padding: 10px 20px;
    }

    @media (max-width: 920px) {
        .Product-sort .row .item-1 {
            width: 270px;
        }

        .category-div {
            display: flex;
            flex-direction: column;
            align-items: self-start;
        }

        .category-div .control-group {
            width: 100%;
            display: flex;
            flex-direction: column;
        }
    }

    @media (max-width: 635px) {

        .content-container .content {
            width: 301px;
        }
    }

    .d-none {
        display: none;
    }
	
	.loader {
	border: 5px solid #f3f3f3;
	border-radius: 50%;
	border-top: 5px solid #c7c7c7;
	width: 45px;
	height: 45px;
	-webkit-animation: spin 2s linear infinite; /* Safari */
	animation: spin 2s linear infinite;
	}

	/* Safari */
	@-webkit-keyframes spin {
	0% { -webkit-transform: rotate(0deg); }
	100% { -webkit-transform: rotate(360deg); }
	}

	@keyframes spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
	}
</style>