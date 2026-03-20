@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="title is-4">Fiche entreprise</h1>
</div>
<div class="tags has-addons is-small mb-3">
    <span class="tag is-dark">Entreprises :</span>
    <span class="tag is-link"><b>{{ $companies_count }}</b></span>
</div>

<div class="tags has-addons is-small mb-3">
    <span class="tag is-dark">Stages :</span>
    <span class="tag is-success"><b>{{ $stages_count }}</b></span>
</div>

<div class="tags has-addons is-small mb-4">
    <span class="tag is-dark">Contacts :</span>
    <span class="tag is-warning"><b>{{ $contacts_count }}</b></span>
</div>

@endsection
