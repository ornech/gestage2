@extends('layouts.app')

@section('content')
<h1>Modifier un contact</h1>

<form method="POST" action="#">
    @csrf
    @method('PUT')

  
</form>
@endsection
