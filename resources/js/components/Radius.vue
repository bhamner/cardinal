<script setup>

  //TODOS: zips and states

  import places from "../models/Places"
  import place_values from "../data/citystates.json"

  import {  onMounted, computed, ref, watch, reactive } from 'vue'

  const { Map } = await google.maps.importLibrary("maps")

  onMounted(() => { initMap(); })
 
  const props = defineProps(['locations']) 
  const locations = reactive(JSON.parse(props.locations))
  const invalidLocations = reactive([])

  const inputText = ref('')

  watch(inputText, async (newInput) => {
    newInput.split(/\r|\n|;/).forEach( function(item) { 
      if(item.length > 3  ){
        let radius = 50
        let unvalidated = item.trim()

        if( item.includes("(") ){
          const locationRadius = item.split('(')
          const regExp = /\(([^)]+)\)/
          const matches = regExp.exec(item)
          radius = matches[1]
          unvalidated = locationRadius[0].trim()
        }

        //if input exists in place_values     
        const validated = place_values.RECORDS.find(({ citystate }) => citystate.toLowerCase().startsWith(unvalidated.toLowerCase()));
        if(validated) { getCoordinates(validated,radius); }
        else{  invalidLocations.push(item)}
     
        //clear text
        inputText.value = "";
      }//end if length > 3
    }) 
  })

 
  let map;
  let circle;

  const initMap = () =>{
      map = new Map(document.getElementById("map-canvas"), {
          center: { lat: 37.0902, lng: -101.299591 },
          zoom: 5,
        });
      addCirclesToMap(map)
  }
  
  function getCoordinates(validated,radius){
    //use axios api to get coords
    places
        .longNameState( encodeURIComponent(validated.citystate) )
        .then(response => {
          //update locations
          const place_info = response.data
          place_info.radius = radius
          //if its already in there, overwrite the radius
          if( locations.some(item => item.long_name === place_info.long_name && item.state === place_info.state )  ){
               const existing = locations.find(item => item.long_name === place_info.long_name && item.state === place_info.state);
               existing.radius = radius;
          }
          else{ locations.push( place_info ); }
          //redraw map
          initMap();
        })
        .catch(function(error) {
          console.log(error);
        });
  }

  const existsInLocations = computed((checkme) => {
     for (const [key, value] of Object.entries(locations)) {
          return value.long_name == checkme 
      }
  })

  function deleteItem(key){
     locations.splice(key, 1)
     initMap();
  }
 


  function addCirclesToMap(map) {
      Object.keys(locations).map(function(objectKey, index) {
        var location =  locations[objectKey]
        var mile_radius = (locations[objectKey]['radius'] !== undefined ) ? locations[objectKey]['radius'] : 50
        const latlng = {
          lat: parseFloat(location.lat),
          lng: parseFloat(location.lng),
        };
        var radius =   mile_radius  * 1609.334; //miles to meters
        // Add the circle for this city to the map.
        circle = new google.maps.Circle({
          strokeColor: "#03b2fb",
          strokeOpacity: 0.8,
          strokeWeight: 1,
          fillColor: "#03b2fb",
          fillOpacity: 0.3,
          map,
          center: latlng,
          radius: radius,
        }); //end citycircle
      });// end obj keys

  } 
</script>

<template>
  
<div class="container flex px-3 py-8  mx-auto">
    <div class="w-full mx-auto flex flex-wrap">
      <div class="flex w-full lg:w-1/2 ">
        <form  class="container px-6 py-8 mx-auto h-fit ">
          <label class="uppercase tracking-wide no-underline hover:no-underline font-bold text-gray-800 text-xl mb-8">Enter addresses </label>
          <p> Format: City, State (radius)</p>
          <p> <i> ex: Ocala, FL (20)</i></p>
          <textarea v-model.lazy="inputText" width="100%" class="block border-box outline outline-neutral-100 resize-none w-full h-full" rows="5"> </textarea>
        </form>
      </div>
        <div class="flex w-full h-fit lg:w-1/4 px-6  py-8">
          <div class="px-3 md:px-0">
              <h4 class="uppercase tracking-wide no-underline hover:no-underline font-bold text-gray-800 text-xl mb-8">Current Locations </h4>
              <ul>
                <li v-for="(value, key) in locations" >
                   {{ value.long_name }}, {{ value.state }} ({{ 'radius' in value ? value['radius'] : 50 }})  
                   <span @click="deleteItem(key)" class="px-2 py-0.5 font-small bg-blue-50 hover:bg-blue-100 hover:text-blue-600 text-blue-500 rounded-sm text-sm"> x </span>
                </li>
              </ul>
          </div>
        </div>
        <div class="flex w-full h-fit lg:w-1/4 px-6  py-8 lg:justify-end  mt-6 md:mt-0">
          <div class="px-3 md:px-0 text-red-600">
            <h4 v-if="invalidLocations.length > 0" class="uppercase tracking-wide no-underline hover:no-underline font-bold text-gray-800 text-xl mb-8">Invalid Locations </h4>
            <ul>
                <li v-for="value in invalidLocations">
                   {{ value }} 
                </li>
              </ul>
          </div>
        </div>
     
    </div>
</div>



  
</template>
