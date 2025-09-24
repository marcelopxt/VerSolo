@extends('layouts.app')

@section('title', 'VerSolo | Início')

@section('content')
  @if (!empty($filename))
    <h1>Nome do Arquivo: {{$filename}}</h1>
  @endif
  <form action="/report" method="post" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
      <label for="" class="form-label">Choose file</label>
      <input type="file" class="form-control" name="file" id="input-file" />
      <br>
      <button type="submit" class="btn btn-outline-dark">Enviar</button>
    </div>
  </form>
@endsection