@extends('default')

@section('content')
  <div class="row">
    <h1>Hakutulokset</h1>

    <div class="col-sm-6">

      <strong>Suomalaiset domainit</strong><br>
      <ul class="list-group result-list">
        @foreach ($domains_fi as $domain)
          <li class="list-group-item">  

            @if (!$domain['status'])
              <i class="fa fa-question-circle pull-right display-inline-block" data-toggle="tooltip" data-placement="bottom" aria-hidden="true"
                title="{{ $domain['whois'] }}"></i>
            @endif

            <span class="icon-container">
              @if ($domain['status'])
                <i class="fa fa-check-circle" aria-hidden="true"></i>
              @endif
            </span>
            {{ $domain['name'] }}

          </li>
        @endforeach
      </ul>

    </div>

    <div class="col-sm-6">

      <strong>Kansainväliset domainit</strong><br>
      <ul class="list-group result-list">
        @foreach ($domains_other as $domain)
          <li class="list-group-item">  

            @if (!$domain['status'])
              <i class="fa fa-question-circle pull-right display-inline-block" data-toggle="tooltip" data-placement="bottom" aria-hidden="true"
                title="{{ $domain['whois'] }}"></i>
            @endif

            <span class="icon-container">
              @if ($domain['status'])
                <i class="fa fa-check-circle" aria-hidden="true"></i>
              @endif
            </span>
            {{ $domain['name'] }}

          </li>
        @endforeach
      </ul>

    </div>
  </div>

  <br>

  <h2>.. tee uusi haku</h2>
  @include('form')


@stop