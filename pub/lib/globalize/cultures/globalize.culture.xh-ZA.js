/**
 * Globalize Culture xh-ZA
 *
 * http://github.com/jquery/globalize
 *
 * Copyright Software Freedom Conservancy, Inc.
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * This file was generated by the Globalize Culture Generator
 * Translation: bugs found in this file need to be fixed in the generator
 */

(function( window, undefined ) {

var Globalize;

if ( typeof require !== "undefined" &&
	typeof exports !== "undefined" &&
	typeof module !== "undefined" ) {
	// Assume CommonJS
	Globalize = require( "globalize" );
} else {
	// Global variable
	Globalize = window.Globalize;
}

Globalize.addCultureInfo( "xh-ZA", "default", {
	name: "xh-ZA",
	englishName: "isiXhosa (South Africa)",
	nativeName: "isiXhosa (uMzantsi Afrika)",
	language: "xh",
	numberFormat: {
		percent: {
			pattern: ["-%n","%n"]
		},
		currency: {
			pattern: ["$-n","$ n"],
			symbol: "R"
		}
	},
	calendars: {
		standard: {
			days: {
				names: ["iCawa","uMvulo","uLwesibini","uLwesithathu","uLwesine","uLwesihlanu","uMgqibelo"],
				namesShort: ["Ca","Mv","Lb","Lt","Ln","Lh","Mg"]
			},
			months: {
				names: ["Mqungu","Mdumba","Kwindla","Tshazimpuzi","Canzibe","Silimela","Khala","Thupha","Msintsi","Dwarha","Nkanga","Mnga",""]
			},
			patterns: {
				d: "yyyy/MM/dd",
				D: "dd MMMM yyyy",
				t: "hh:mm tt",
				T: "hh:mm:ss tt",
				f: "dd MMMM yyyy hh:mm tt",
				F: "dd MMMM yyyy hh:mm:ss tt",
				M: "dd MMMM",
				Y: "MMMM yyyy"
			}
		}
	}
});

}( this ));
