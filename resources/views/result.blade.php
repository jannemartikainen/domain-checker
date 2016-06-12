@extends('default')

@section('content')
  
  <h1>Hakutulokset</h1>
  <ul class="list-group result-list">
    @foreach ($domains as $domain)
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
  <br>

  <h2>.. tee uusi haku</h2>
  @include('form')


@stop