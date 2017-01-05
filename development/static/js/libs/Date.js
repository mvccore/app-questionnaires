if (!Date.unixTimestamp) {
	Date.unixTimestamp = function () {
		return Math.round(+new Date / 1000);
	}
}