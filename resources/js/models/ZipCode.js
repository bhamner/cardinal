class ZipCodes {


	static nearby(location,radius){
		return axios.get('/api/nearby-zip-codes?&radius='+radius+'&location='+location);
	}

	static within(location){
		return axios.get('/api/zip-codes-in-boundary?&location='+location);
	}

	static on(location){
		return axios.post('/api/zip-codes-on-route',{location});
	}

	static state(state){
		return axios.get('/api/state-zip-codes/'+state);
	}

}

export default ZipCodes;