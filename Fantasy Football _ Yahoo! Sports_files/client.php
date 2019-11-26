
(function(win, input){

	function base64_decode(s){
		// for modern browsers
		// TODO: test the worst case (i.e. the custom code) if we are requesting this with phantomJS for testing
		if( win.atob ) return win.atob(s);
		// for IE and some mobile ones
		var out = "",
			chr1, chr2, chr3,
			enc1, enc2, enc3, enc4,
			i,len=s.length, iO='indexOf',cA='charAt', fCC=String.fromCharCode,
			lut = "ABCDEFGHIJKLMNOP" +
			      "QRSTUVWXYZabcdef" +
			      "ghijklmnopqrstuv" +
			      "wxyz0123456789+/" +
			      "=";
		for(i=0;i<len;){
			// get the encoded bytes
			enc1 = lut[iO](s[cA](i++));
			enc2 = lut[iO](s[cA](i++));
			enc3 = lut[iO](s[cA](i++));
			enc4 = lut[iO](s[cA](i++));
			// turn them into chars
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
			out += fCC(chr1);
			if (enc3 != 64) {
				out += fCC(chr2);
			}
			if (enc4 != 64) {
				out += fCC(chr3);
			}
		}
		return out;
	}
	/**
	 * Load a script in HEAD
	 *
	 * pass either uri or inner. one will set the SRC the other the .text
	 */
	function loadScript(uri, inner, sf) {
		var h = document.getElementsByTagName('head')[0] || document.documentElement,
			s = document.createElement('script');
		if( !sf ){
			s.type = 'text/javascript';
		}else{
			s.type = 'text/x-safeframe';
		}
		if( inner ){
			s.text = inner;
		}else{
			s.src = uri;
		}
		return h.appendChild(s);
	}

	/* TODO: pass input as plain JSON, not a string... and then assign it to
	 * win.DARLA_CONFIG=input;
	 * and call a new public method that will parse the positions list (currently inline-code in boot.js:_get_tags()
	*/
	loadScript( false, base64_decode(input), true );
	loadScript( "https://s.yimg.com/rq/darla/boot.js", false, false);

}(window, "eyJwb3NpdGlvbnMiOiBbXSwgImNvbmYiOnsidXNlWUFDIjowLCJ1c2VQRSI6MSwic2VydmljZVBhdGgiOiIiLCJ4c2VydmljZVBhdGgiOiIiLCJiZWFjb25QYXRoIjoiIiwicmVuZGVyUGF0aCI6IiIsImFsbG93RmlGIjpmYWxzZSwic3JlbmRlclBhdGgiOiJodHRwczpcL1wvcy55aW1nLmNvbVwvcnFcL2RhcmxhXC8zLTQtM1wvaHRtbFwvci1zZi5odG1sIiwicmVuZGVyRmlsZSI6Imh0dHBzOlwvXC9zLnlpbWcuY29tXC9ycVwvZGFybGFcLzMtNC0zXC9odG1sXC9yLXNmLmh0bWwiLCJzZmJyZW5kZXJQYXRoIjoiaHR0cHM6XC9cL3MueWltZy5jb21cL3JxXC9kYXJsYVwvMy00LTNcL2h0bWxcL3Itc2YuaHRtbCIsIm1zZ1BhdGgiOiJodHRwczpcL1wvZmMueWFob28uY29tXC91bnN1cHBvcnRlZC0xOTQ2Lmh0bWwiLCJjc2NQYXRoIjoiaHR0cHM6XC9cL3MueWltZy5jb21cL3JxXC9kYXJsYVwvMy00LTNcL2h0bWxcL3ItY3NjLmh0bWwiLCJyb290Ijoic2RhcmxhIiwiZWRnZVJvb3QiOiJodHRwOlwvXC9sLnlpbWcuY29tXC9ycVwvZGFybGFcLzMtNC0zIiwic2VkZ2VSb290IjoiaHR0cHM6XC9cL3MueWltZy5jb21cL3JxXC9kYXJsYVwvMy00LTMiLCJ2ZXJzaW9uIjoiMy00LTMiLCJ0cGJVUkkiOiIiLCJob3N0RmlsZSI6Imh0dHBzOlwvXC9zLnlpbWcuY29tXC9ycVwvZGFybGFcLzMtNC0zXC9qc1wvZy1yLW1pbi5qcyIsImJlYWNvbnNEaXNhYmxlZCI6dHJ1ZSwicm90YXRpb25UaW1pbmdEaXNhYmxlZCI6dHJ1ZSwiZmRiX2xvY2FsZSI6IldoYXQgZG9uJ3QgeW91IGxpa2UgYWJvdXQgdGhpcyBhZD98SXQncyBvZmZlbnNpdmV8U29tZXRoaW5nIGVsc2V8VGhhbmsgeW91IGZvciBoZWxwaW5nIHVzIGltcHJvdmUgeW91ciBZYWhvbyBleHBlcmllbmNlfEl0J3Mgbm90IHJlbGV2YW50fEl0J3MgZGlzdHJhY3Rpbmd8SSBkb24ndCBsaWtlIHRoaXMgYWR8U2VuZHxEb25lfFdoeSBkbyBJIHNlZSBhZHM/fExlYXJuIG1vcmUgYWJvdXQgeW91ciBmZWVkYmFjay58V2FudCBhbiBhZC1mcmVlIGluYm94PyBVcGdyYWRlIHRvIFlhaG9vIE1haWwgUHJvIXxVcGdyYWRlIE5vdyIsInByb3BlcnR5IjoiIiwiZXZlbnRzIjpbXSwibGFuZyI6ImVuLXVzIiwic3BhY2VJRCI6Ijc4MjIwMzc4OSIsImRlYnVnIjpmYWxzZX19"));

