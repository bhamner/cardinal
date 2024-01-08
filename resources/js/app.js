import './bootstrap';

 
import { createApp } from 'vue/dist/vue.esm-bundler';
const app = createApp({})
  
import Radius from './components/Radius.vue'


const RootComponent = {
      components: {
          Radius,
      },
};



createApp(RootComponent).mount("#vuebox")

