<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name') }}</title>
    <!--<meta name="description" content="">
    <meta name="keywords" content="">-->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite('resources/css/app.css')
    @vite('resources/css/styles.css')
    <link href="https://fonts.googleapis.com/css?family=Work+Sans:200,400&display=swap" rel="stylesheet">
 

</head>

<body class="bg-white text-gray-600 work-sans leading-normal text-base tracking-normal">
	@include('layout.nav')
	@yield('content')
	@include('layout.footer')
   @vite('resources/js/app.js')
  
   <script>
        (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
          key: "{{ config('services.googlemaps.key') }}",
          v: "weekly",
          // Use the 'v' parameter to indicate the version to use (weekly, beta, alpha, etc.).
          // Add other bootstrap parameters as needed, using camel case.
        });
   </script>
 
</body>
</html>