@if(session('success'))
    <div class="notification is-success is-light mb-4">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="notification is-danger is-light mb-4">
        @foreach($errors->all() as $erreur)<p>{{ $erreur }}</p>@endforeach
    </div>
@endif
