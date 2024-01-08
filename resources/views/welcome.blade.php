@extends('layout.app')
@section('content')

<section id="map-canvas" class="w-full mx-auto bg-nordic-gray-light flex pt-12 md:pt-0 md:items-center" style="max-width:1600px; height: 32rem;  ">
</section>

<section id="vuebox" class="bg-white py-8"> 
 <Suspense> <Radius locations="{{ json_encode(\App\Models\Place::where('state','AL')->take(1)->get()) }}"/></Suspense>
</section>
 



@endsection