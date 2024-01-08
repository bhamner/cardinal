class Places {
	static validate(place){
		return axios.get('/api/places/'+place);
	}
	static longNameState(validatedStr){
		return axios.get('/api/places/long_name_state?location='+validatedStr  );
	}
}

export default Places;