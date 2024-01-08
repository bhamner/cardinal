class CLMarkets {


	static nearby(location,radius){
		return axios.get('/api/nearby-craigslist-markets?&radius='+radius+'&location='+location);
	}

	static state(state){
		return axios.get('/api/state-craigslist-markets/'+state);
	}

	static within(location){
		return axios.get('/api/craigslist-markets-in-boundary?location='+location);
	}

	static on(location){
		return axios.post('/api/craigslist-markets-on-route',{location});
	}



}

export default CLMarkets;