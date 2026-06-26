@extends('layouts.app')
@section('title', isset($site) ? 'Éditer '.$site->name : 'Nouveau site — QRMap')

@section('content')
<div class="breadcrumb">
    <a href="#">Sites</a>
    <span class="sep">/</span>
    <span>{{ isset($site) ? 'Éditer' : 'Nouveau' }}</span>
</div>

<div class="page-header">
    <h1 class="page-title">{{ isset($site) ? 'Éditer le site' : 'Nouveau <span>site</span>' }}</h1>
</div>

<div class="card" style="max-width: 640px;">
    <form method="POST" action="{{ isset($site) ? route('sites.update', $site) : route('sites.store') }}">
        @csrf
        @if(isset($site)) @method('PUT') @endif

        <div class="form-group">
            <label>Nom du site *</label>
            <input type="text" name="name" value="{{ old('name', $site->name ?? '') }}" placeholder="Ex: Campus principal, Siège social..." required>
            @error('name')<span style="color: var(--danger); font-size: 12px;">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label>Adresse</label>
            <input type="text" name="address" value="{{ old('address', $site->address ?? '') }}" placeholder="Rue, ville, pays">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Latitude</label>
                <input type="number" name="latitude" step="0.0000001" value="{{ old('latitude', $site->latitude ?? '') }}" placeholder="Ex: 3.8667">
            </div>
            <div class="form-group">
                <label>Longitude</label>
                <input type="number" name="longitude" step="0.0000001" value="{{ old('longitude', $site->longitude ?? '') }}" placeholder="Ex: 11.5167">
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Description du site, informations utiles...">{{ old('description', $site->description ?? '') }}</textarea>
        </div>

        <hr class="divider">
        <div class="btn-actions">
            <button type="submit" class="btn btn-primary">{{ isset($site) ? 'Enregistrer' : 'Créer le site' }}</button>
            <a href="{{ isset($site) ? route('sites.show', $site) :  }}" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>
@endsection