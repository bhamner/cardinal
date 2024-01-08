class HireData {

	static on(zips){
		return axios.post('/api/hires-by-product',{zips});
	}



}

export default HireData;