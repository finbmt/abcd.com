@extends('dashboard')

@section('template_title')
	Tỷ số bóng đá
@endsection

@section('template_linked_css')

@endsection

@section('template_fastload_css')
@endsection

@section('header')
	Tỷ số bóng đá
@endsection

@section('breadcrumbs')
	<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
		<a itemprop="item" href="{{url('/')}}">
			<span itemprop="name">
				{{ Lang::get('titles.app') }}
			</span>
		</a>
		<i class="material-icons">chevron_right</i>
		<meta itemprop="position" content="1" />
	</li>
	<li class="active" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
		<a itemprop="item" href="{{url('scores')}}" disabled>
			<span itemprop="name">
				Tỷ số bóng đá
			</span>
		</a>
		<meta itemprop="position" content="2" />
	</li>
@endsection

@section('content')

<div class="mdl-card mdl-shadow--2dp mdl-cell mdl-cell--12-col mdl-cell--8-col-tablet mdl-cell--12-col-desktop margin-top-0">
	<div class="mdl-card__title mdl-color--primary mdl-color-text--white">
		<h2 class="mdl-card__title-text logo-style">
			@if ($total_scores === 1)
			    {{ $total_scores }} Trận đấu
			@elseif ($total_scores > 1)
			    {{ $total_scores }} Trận đấu
			@else
			    Không có dữ liệu :(
			@endif
		</h2>
	</div>
	<div class="mdl-card__supporting-text mdl-color-text--grey-600 padding-0">
		<div class="table-responsive material-table">
			<table id="user_table" class="mdl-data-table mdl-js-data-table data-table" cellspacing="0" width="100%">
			  <thead>
			    <tr>
					<th class="mdl-data-table__cell--non-numeric">Thời<br>Gian</th>
					<th class="mdl-data-table__cell--non-numeric">Tình<br>Trạng</th>
					<th class="mdl-data-table__cell--non-numeric">Đội Nhà</th>
					<th class="mdl-data-table__cell--non-numeric">Tỷ Số</th>
					<th class="mdl-data-table__cell--non-numeric">Đội Khách</th>
					<th class="mdl-data-table__cell--non-numeric">C/H-T</th>
					<th class="mdl-data-table__cell--non-numeric">Dữ liệu</th>
					<th class="mdl-data-table__cell--non-numeric no-sort no-search">Đặt Cược</th>
			    </tr>
			  </thead>
			  <tbody>
			        @foreach ($scores as $item)
						<tr>
							<td colspan="8" class="mdl-data-table__cell--non-numeric" style="color:#fff;background-color:{{ $item['League']['color'] }}">
								 <span class="mdl-list__item"><i class="material-icons">&#xE315;</i> <b>{{ $item['League']['fullName'] }}</b></span>
							</td>
						</tr>
						@foreach ($item['Match'] as $league)
						<tr>
							<td class="mdl-data-table__cell--non-numeric">{{ $league['MatchTime'] }}</td>
							<td class="mdl-data-table__cell--non-numeric">{!! $league['mState'] !!}</td>
							<td class="mdl-data-table__cell--non-numeric">{{ $league['hName'] }}</td>
							<td class="mdl-data-table__cell--non-numeric">{{ $league['hScore'] }} - {{ $league['gScore'] }}</td>
							<td class="mdl-data-table__cell--non-numeric">{{ $league['gName'] }}</td>
							<td class="mdl-data-table__cell--non-numeric"></td>
							<td class="mdl-data-table__cell--non-numeric"></td>
							<td class="mdl-data-table__cell--non-numeric">

							</td>
						</tr>
						@endforeach
			        @endforeach
			  </tbody>
			</table>
		</div>
	</div>
    <div class="mdl-card__menu" style="top: -5px;">
		<a href="{{ url('/users/create') }}" class="mdl-button mdl-button--icon mdl-inline-expanded mdl-js-button mdl-js-ripple-effect mdl-button--icon mdl-color-text--white inline-block">
			<i class="material-icons">person_add</i>
		</a>
		<div class="mdl-textfield mdl-js-textfield mdl-textfield--expandable search-white">
			<label class="mdl-button mdl-button--icon mdl-js-button mdl-js-ripple-effect mdl-button--icon" for="search_table">
			  	<i class="material-icons">search</i>
			</label>
			<div class="mdl-textfield__expandable-holder">
			  	<input class="mdl-textfield__input" type="search" id="search_table" placeholder="Search Terms">
			  	<label class="mdl-textfield__label" for="search_table">
			  		Search Terms
			  	</label>
			</div>
		</div>
    </div>
</div>

@include('dialogs.dialog-delete')

@endsection

@section('template_scripts')


@endsection