@extends('admin::layouts.content')

@section('page_title')
{{ __('admin::app.catalog.products.title') }}
@stop

@section('content')

<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h1>Override Shipping Date</h1>
        </div>
    </div>
     <form method="post" action="{{ route('admin.catalog.shipping_date.update') }}" id="form" >
	 @csrf
        <label for="date">Shipping Date</label>
        <input type="date" name="date" value="{{$shippingDate}}" min="{{date('Y-m-d')}}" required>
        <div class="submit-div">
			<button type="submit" class="btn btn-primary submit_form">Enable</button>
			@if($status=='1')
			<a href="{{ route('admin.catalog.shipping_date.delete') }}" class="btn btn-primary delete-button">Disable</a>
		    @endif
		</div>
    </form>
</div>

@stop

<style>
	form {
		background-color: #fff;
		padding: 20px;
		border-radius: 8px;
		box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
	}

	label {
		display: block;
		margin-bottom: 8px;
	}

	input[type="date"] {
		padding: 8px;
		margin-bottom: 16px;
		box-sizing: border-box;
	}

	.submit-div {
        width: 100%;
        display: flex;
    }

    .submit-div button {
        margin-top: 20px;
        margin-right: 10px;
        padding: 10px 20px;
    }
	
	.delete-button {
	  display: inline-block;
	  margin-top: 20px;
	  padding: 10px 15px;
	}
</style>