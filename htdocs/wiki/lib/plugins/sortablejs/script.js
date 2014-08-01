/*
	SortTable
  version 2.1
  7th April 2007
  Stuart Langridge, http://www.kryogenix.org/code/browser/sorttable/

  19 Feb 2008
  Fixed some jslint errors to support DokuWiki (http://www.splitbrain.org) js compression

  function reinitsort()
  sorttable.reinit
  added by Otto Vainio to allow sort tables updated with javascript.
  Otto Vainio (otto@valjakko.net)

  27.11.2008
  Changed line 77 document.getElementsByTagName('table') to div.getElementsByTagName('table')
  To allow multiple sortable tables in same page
  (Thanks to Hans Sampiemon)

  14.1.2009
  Added option for default sorting.
  Use dokuwiki event registration.

  27.1.2009
  Cleaned some jlint errors to make this workable, when css+js compress is set in dokuwiki

  10.5.2011
 * version 2.5 Fixed problems with secionediting, footnotes and edittable

  18.7.2013
 * version 2.6 Added support for jQuery and dokuwiki Weatherwax ->

  28.5.2014 
  * version 2.7 Fixed problem with first row not getting sorted

  30.5.2014 
  * version 2.8 Fixed problem with first row not getting sorted in default sort. Added option "sumrow" to prevent sum line sort. 


  Instructions:
  Used from dokuwiki 
  Click on the headers to sort
  
  Thanks to many, many people for contributions and suggestions.
  Licenced as X11: http://www.kryogenix.org/code/browser/licence.html
  This basically means: do what you want with it.
*/
 
var stIsIE = /*@cc_on!@*/false;
var tableid = 0;

sorttable = {
  reinit: function() {
    arguments.callee.done = true;
    // kill the timer
    //if (_timer) {clearInterval(_timer);}
    
    if (!document.createElement || !document.getElementsByTagName) {return;}
    
//    sorttable.DATE_RE = /^(\d\d?)[\/\.\-](\d\d?)[\/\.\-]((\d\d)?\d\d)$/;
    sorttable.DATE_RE = /^(\d\d?)[\/\.\-](\d\d?)[\/\.\-]((\d\d)?\d\d)( (\d\d?)[:\.]?(\d\d?))?$/;

    
    forEach(document.getElementsByTagName('table'), function(table) {
      if (table.className.search(/\bsortable\b/) != -1) {
        sorttable.makeSortable(table);
      }
    });
    forEach(document.getElementsByTagName('div'), function(div) {
      if (div.className.search(/\bsortable\b/) != -1) {
        sorttable.makeSortablediv(div);
      }
    });
  },

  init: function() {
    // quit if this function has already been called
    if (arguments.callee.done) {return;}
    // flag this function so we don't do the same thing twice
    arguments.callee.done = true;
    // kill the timer
    //if (_timer) {clearInterval(_timer);}
    
    if (!document.createElement || !document.getElementsByTagName) {return;}
    
//    sorttable.DATE_RE = /^(\d\d?)[\/\.\-](\d\d?)[\/\.\-]((\d\d)?\d\d)$/;
    sorttable.DATE_RE = /^(\d\d?)[\/\.\-](\d\d?)[\/\.\-]((\d\d)?\d\d)( (\d\d?):?(\d\d?))?$/;
    
    forEach(document.getElementsByTagName('table'), function(table) {
      if (table.className.search(/\bsortable\b/) != -1) {
        sorttable.makeSortable(table);
      }
    });
    forEach(document.getElementsByTagName('div'), function(div) {
      if (div.className.search(/\bsortable\b/) != -1) {
        sorttable.makeSortablediv(div);
      }
    });
    
  },
  makeSortablediv: function(div) {
        if (div.getElementsByTagName('table').length === 0) {
        } else {
          forEach(div.getElementsByTagName('table'), function(table) {
            colid=div.className;
            overs = new Array();
            var patt1=/\bcol_\d_[a-z]+/gi;
            var overs = new Array();
            if (colid.search(patt1) != -1) {
              var overrides = new Array();
              overrides = colid.match(patt1);
              var xo="";
              for (xo in overrides) 
              {
                if (xo == "")
                {
                } else {
                  try
                  {
                    var tmp = overrides[xo].split("_");
                    var ind = tmp[1];
                    var val = tmp[2];
                    overs[ind]=val;
                  	
                  }
                  catch (e)
                  {
                  }
                }
              }
              colid = colid.replace(patt1,'');
            }
            var patt2=/\bsortbottom/gi;
            var bottoms = 0;
            if (colid.search(patt2) != -1) {
				bottoms=1;
			}
            sorttable.makeSortable(table,overs,bottoms);
            if (colid.search(/\bsort/) != -1) {
              colid = colid.replace('sortable','');
              colid = colid.replace(' sort','');
              if (!colid != '')
              {
                colid = colid.trim();
              }
              revs=false;
              if (colid.search(/\br/) != -1) {
                revs=true;
                colid = colid.replace('r','');
              }
              sorttable.defaultSort(table,colid,revs);
            }
          });
        }
  },
  defaultSort: function(table, colid, revs) {
//    theadrow = table.tHead.rows[0].cells;
    theadrow = table.rows[0].cells;
    colid--;
    colname ="col"+colid;
     // remove sorttable_sorted classes
     var thiscell=false;
     forEach(theadrow, function(cell) {
       colclass=cell.className;               
       classname = colclass.split(" ");       
       if (classname[0]==colname)             
//       if (cell.className==colname)
       {
         thiscell=cell;
       }
//       if (cell.nodeType == 1) { // an element
//         cell.className = cell.className.replace('sorttable_sorted_reverse','');
//         cell.className = cell.className.replace('sorttable_sorted','');
//       }
     });
     if (thiscell===false) {return;}
     sortfwdind = document.getElementById('sorttable_sortfwdind');
     if (sortfwdind) { sortfwdind.parentNode.removeChild(sortfwdind); }
     sortrevind = document.getElementById('sorttable_sortrevind');
     if (sortrevind) { sortrevind.parentNode.removeChild(sortrevind); }
     
     thiscell.className += ' sorttable_sorted';
     sortfwdind = document.createElement('span');
     sortfwdind.id = "sorttable_sortfwdind";
     sortfwdind.innerHTML = stIsIE ? '&nbsp<font face="webdings">6</font>' : '&nbsp;&#x25BE;';
     thiscell.appendChild(sortfwdind);
 
     // build an array to sort. This is a Schwartzian transform thing,
     // i.e., we "decorate" each row with the actual sort key,
     // sort based on the sort keys, and then put the rows back in order
     // which is a lot faster because you only do getInnerText once per row
     row_array = [];
     col = thiscell.sorttable_columnindex;
     rows = thiscell.sorttable_tbody.rows;
     for (var j=0; j<rows.length; j++) {
       row_array[row_array.length] = [sorttable.getInnerText(rows[j].cells[col]), rows[j]];
     }
     /* If you want a stable sort, uncomment the following line */
     //sorttable.shaker_sort(row_array, this.sorttable_sortfunction);
     /* and comment out this one */
     row_array.sort(thiscell.sorttable_sortfunction);
     
     tb = thiscell.sorttable_tbody;
     for (var jj=0; jj<row_array.length; jj++) {
       tb.appendChild(row_array[jj][1]);
     }
     
     delete row_array;
     // If reverse sort wanted, then doit
     if (revs) {
      // reverse the table, which is quicker
       sorttable.reverse(thiscell.sorttable_tbody);
       thiscell.className = thiscell.className.replace('sorttable_sorted',
                                                       'sorttable_sorted_reverse');
       thiscell.removeChild(document.getElementById('sorttable_sortfwdind'));
       sortrevind = document.createElement('span');
       sortrevind.id = "sorttable_sortrevind";
       sortrevind.innerHTML = stIsIE ? '&nbsp<font face="webdings">5</font>' : '&nbsp;&#x25B4;';
       thiscell.appendChild(sortrevind);
     }



  },

  makeSortable: function(table,overrides, bottoms) {
//    tableid++;
/*
    if (table.getElementsByTagName('thead').length === 0) {
      // table doesn't have a tHead. Since it should have, create one and
      // put the first table row in it.
      the = document.createElement('thead');
      the.appendChild(table.rows[0]);
      table.insertBefore(the,table.firstChild);
    }
*/
    // Safari doesn't support table.tHead, sigh
/*
    if (table.tHead === null) {table.tHead = table.getElementsByTagName('thead')[0];}
    
    if (table.tHead.rows.length != 1) {return;} // can't cope with two header rows
  */  
//    table.tHead.className += ' tableid'+tableid;

    // Sorttable v1 put rows with a class of "sortbottom" at the bottom (as
    // "total" rows, for example). This is B&R, since what you're supposed
    // to do is put them in a tfoot. So, if there are sortbottom rows,
    // for backwards compatibility, move them to tfoot (creating it if needed).
    
    sortbottomrows = [];
	if (bottoms>0) {
		frombottom=0;
		for (var i=table.rows.length; i>0; i--) {
//		  if (table.rows[i].className.search(/\bsortbottom\b/) != -1) {
          if (bottoms==frombottom) {
			sortbottomrows[sortbottomrows.length] = table.rows[i];
		  }
		  frombottom++;
		}
		if (sortbottomrows) {
		  if (table.tFoot === null) {
			// table doesn't have a tfoot. Create one.
			tfo = document.createElement('tfoot');
			table.appendChild(tfo);
		  }
		  for (var ii=0; ii<sortbottomrows.length; ii++) {
			tfo.appendChild(sortbottomrows[ii]);
		  }
		  delete sortbottomrows;
		}
    }
    // work through each column and calculate its type
//    headrow = table.tHead.rows[0].cells;
    headrow = table.rows[0].cells;
//    for (var i=0; i<headrow.length; i++) {
    for (i=0; i<headrow.length; i++) {
      // manually override the type with a sorttable_type attribute
      var colOptions="";
      if (overrides[i+1])
      {
        colOptions=overrides[i+1];
      }
      if (!colOptions.match(/\bnosort\b/)) { // skip this col
        mtch = colOptions.match(/\b[a-z0-9]+\b/);
        if (mtch) { override = mtch[0]; }
        if (mtch && typeof sorttable["sort_"+override] == 'function') {
          headrow[i].sorttable_sortfunction = sorttable["sort_"+override];
        } else {
          headrow[i].sorttable_sortfunction = sorttable.guessType(table,i);
        }
/*      
      if (!headrow[i].className.match(/\bsorttable_nosort\b/)) { // skip this col
        mtch = headrow[i].className.match(/\bsorttable_([a-z0-9]+)\b/);
        if (mtch) { override = mtch[1]; }
        if (mtch && typeof sorttable["sort_"+override] == 'function') {
          headrow[i].sorttable_sortfunction = sorttable["sort_"+override];
        } else {
          headrow[i].sorttable_sortfunction = sorttable.guessType(table,i);
        }
*/
        // make it clickable to sort
        headrow[i].sorttable_columnindex = i;
        headrow[i].sorttable_tbody = table.tBodies[0];
//        dean_addEvent(headrow[i],"click", function(e) {
//        addEvent(headrow[i],"click", function(e) {
        jQuery(headrow[i]).click(function(){ 

          theadrow = this.parentNode;

          if (this.className.search(/\bsorttable_sorted\b/) != -1) {
            // if we're already sorted by this column, just 
            // reverse the table, which is quicker
            sorttable.reverse(this.sorttable_tbody);
            this.className = this.className.replace('sorttable_sorted',
                                                    'sorttable_sorted_reverse');
            sortfwdind = document.getElementById('sorttable_sortfwdind');
            if (sortfwdind) { sortfwdind.parentNode.removeChild(sortfwdind); }
//            this.removeChild(document.getElementById('sorttable_sortfwdind'));
            sortrevind = document.getElementById('sorttable_sortrevind');
            if (sortrevind) { sortrevind.parentNode.removeChild(sortrevind); }
            sortrevind = document.createElement('span');
            sortrevind.id = "sorttable_sortrevind";
            sortrevind.innerHTML = stIsIE ? '&nbsp<font face="webdings">5</font>' : '&nbsp;&#x25B4;';
            this.appendChild(sortrevind);
            return;
          }
          if (this.className.search(/\bsorttable_sorted_reverse\b/) != -1) {
            // if we're already sorted by this column in reverse, just 
            // re-reverse the table, which is quicker
            sorttable.reverse(this.sorttable_tbody);
            this.className = this.className.replace('sorttable_sorted_reverse',
                                                    'sorttable_sorted');
            sortrevind = document.getElementById('sorttable_sortrevind');
            if (sortrevind) { sortrevind.parentNode.removeChild(sortrevind); }
//            this.removeChild(document.getElementById('sorttable_sortrevind'));
            sortfwdind = document.getElementById('sorttable_sortfwdind');
            if (sortfwdind) { sortfwdind.parentNode.removeChild(sortfwdind); }
            sortfwdind = document.createElement('span');
            sortfwdind.id = "sorttable_sortfwdind";
            sortfwdind.innerHTML = stIsIE ? '&nbsp<font face="webdings">6</font>' : '&nbsp;&#x25BE;';
            this.appendChild(sortfwdind);
            return;
          }
          
          // remove sorttable_sorted classes
//          theadrow = this.parentNode;
          forEach(theadrow.childNodes, function(cell) {
            if (cell.nodeType == 1) { // an element
              cell.className = cell.className.replace('sorttable_sorted_reverse','');
              cell.className = cell.className.replace('sorttable_sorted','');
            }
          });
          sortfwdind = document.getElementById('sorttable_sortfwdind');
          if (sortfwdind) { sortfwdind.parentNode.removeChild(sortfwdind); }
          sortrevind = document.getElementById('sorttable_sortrevind');
          if (sortrevind) { sortrevind.parentNode.removeChild(sortrevind); }
          
          this.className += ' sorttable_sorted';
          sortfwdind = document.createElement('span');
          sortfwdind.id = "sorttable_sortfwdind";
          sortfwdind.innerHTML = stIsIE ? '&nbsp<font face="webdings">6</font>' : '&nbsp;&#x25BE;';
          this.appendChild(sortfwdind);

          // build an array to sort. This is a Schwartzian transform thing,
          // i.e., we "decorate" each row with the actual sort key,
          // sort based on the sort keys, and then put the rows back in order
          // which is a lot faster because you only do getInnerText once per row
          row_array = [];
          col = this.sorttable_columnindex;
          rows = this.sorttable_tbody.rows;
          for (var j=0; j<rows.length; j++) {
            row_array[row_array.length] = [sorttable.getInnerText(rows[j].cells[col]), rows[j]];
          }
          /* If you want a stable sort, uncomment the following line */
          //sorttable.shaker_sort(row_array, this.sorttable_sortfunction);
          /* and comment out this one */
          row_array.sort(this.sorttable_sortfunction);
          
          tb = this.sorttable_tbody;
          for (var j3=0; j3<row_array.length; j3++) {
            tb.appendChild(row_array[j3][1]);
          }
          
          delete row_array;
        });
      }
    }
  },
  
  guessType: function(table, column) {
    // guess the type of a column based on its first non-blank row
  var NONE=0;
	var TEXT=0;
	var NUM=0;
	var DDMM=0;
	var MMDD=0;
    sortfn = sorttable.sort_alpha;
    for (var i=0; i<table.tBodies[0].rows.length; i++) {
      text = sorttable.getInnerText(table.tBodies[0].rows[i].cells[column]);
      set=0;
      if (text !== '') {
        if (text.match(/^-?[£$¤]?[\d,.]+[%€]?$/)) {
          set=1;
          NUM=1;
        }
        // check for a date: dd/mm/yyyy or dd/mm/yy 
        // can have / or . or - as separator
        // can be mm/dd as well
        possdate = text.match(sorttable.DATE_RE);
        if (possdate) {
          // looks like a date
          first = parseInt(possdate[1]);
          second = parseInt(possdate[2]);
          if (first > 12) {
            // definitely dd/mm
//            return sorttable.sort_ddmm;
            set=1;
            DDMM=1;
          } else if (second > 12) {
            set=1;
            MMDD=1;
//            return sorttable.sort_mmdd;
          } else {
            // looks like a date, but we can't tell which, so assume
            // that it's dd/mm (English imperialism!) and keep looking
            set=1;
            DDMM=1;
//            sortfn = sorttable.sort_ddmm;
          }
        }
        // if nothing known then assume text
        if (set==0) {
          TEXT=1;
        }
        set=0;

      }
    }
    if (TEXT>0 || NUM+DDMM+MMDD>1) return sorttable.sort_alpha;
    if (NUM>0) return sorttable.sort_numeric;
    if (DDMM>0) return sorttable.sort_ddmm;
    if (MMDD>0) return sorttable.sort_mmdd;
  },
  
  getInnerText: function(node) {
    // gets the text we want to use for sorting for a cell.
    // strips leading and trailing whitespace.
    // this is *not* a generic getInnerText function; it's special to sorttable.
    // for example, you can override the cell text with a customkey attribute.
    // it also gets .value for <input> fields.
    
    hasInputs = (typeof node.getElementsByTagName == 'function') &&
                 node.getElementsByTagName('input').length;
    
    if (node.getAttribute("sorttable_customkey") !== null) {
      return node.getAttribute("sorttable_customkey");
    }
    else if (typeof node.textContent != 'undefined' && !hasInputs) {
      return node.textContent.replace(/^\s+|\s+$/g, '');
    }
    else if (typeof node.innerText != 'undefined' && !hasInputs) {
      return node.innerText.replace(/^\s+|\s+$/g, '');
    }
    else if (typeof node.text != 'undefined' && !hasInputs) {
      return node.text.replace(/^\s+|\s+$/g, '');
    }
    else {
      switch (node.nodeType) {
        case 3:
          if (node.nodeName.toLowerCase() == 'input') {
            return node.value.replace(/^\s+|\s+$/g, '');
          }
        case 4:
          return node.nodeValue.replace(/^\s+|\s+$/g, '');
          break;
        case 1:
        case 11:
          var innerText = '';
          for (var i = 0; i < node.childNodes.length; i++) {
            innerText += sorttable.getInnerText(node.childNodes[i]);
          }
          return innerText.replace(/^\s+|\s+$/g, '');
          break;
        default:
          return '';
      }
    }
  },
  
  reverse: function(tbody) {
    // reverse the rows in a tbody
    newrows = [];
    for (var i=0; i<tbody.rows.length; i++) {
      newrows[newrows.length] = tbody.rows[i];
    }
    for (var i=newrows.length-1; i>=0; i--) {
       tbody.appendChild(newrows[i]);
    }
    delete newrows;
  },
  
  /* sort functions
     each sort function takes two parameters, a and b
     you are comparing a[0] and b[0] */
  sort_numeric: function(a,b) {
    aa = parseFloat(a[0].replace(/[^0-9.\-]/g,''));
    if (isNaN(aa)) {aa = 0;}
    bb = parseFloat(b[0].replace(/[^0-9.\-]/g,'')); 
    if (isNaN(bb)) {bb = 0;}
    return aa-bb;
  },
  sort_alpha: function(a,b) {
    if (a[0]==b[0]) {return 0;}
    if (a[0]<b[0]) {return -1;}
    return 1;
  },
  sort_ddmm: function(a,b) {
    mtch = a[0].match(sorttable.DATE_RE);
    y = mtch[3]; m = mtch[2]; d = mtch[1];
    t = mtch[5]+'';
    if (t.length < 1 ) {t = '';}
    if (m.length == 1) {m = '0'+m;}
    if (d.length == 1) {d = '0'+d;}
    dt1 = y+m+d+t;
    mtch = b[0].match(sorttable.DATE_RE);
    y = mtch[3]; m = mtch[2]; d = mtch[1];
    t = mtch[5]+'';
    if (t.length < 1 ) {t = '';}
    if (m.length == 1) {m = '0'+m;}
    if (d.length == 1) {d = '0'+d;}
    dt2 = y+m+d+t;
    if (dt1==dt2) {return 0;}
    if (dt1<dt2) {return -1;}
    return 1;
  },
  sort_mmdd: function(a,b) {
    mtch = a[0].match(sorttable.DATE_RE);
    y = mtch[3]; d = mtch[2]; m = mtch[1];
    t = mtch[5]+'';
    if (m.length == 1) {m = '0'+m;}
    if (d.length == 1) {d = '0'+d;}
    dt1 = y+m+d+t;
    mtch = b[0].match(sorttable.DATE_RE);
    y = mtch[3]; d = mtch[2]; m = mtch[1];
    t = mtch[5]+'';
    if (t.length < 1 ) {t = '';}
    if (m.length == 1) {m = '0'+m;}
    if (d.length == 1) {d = '0'+d;}
    dt2 = y+m+d+t;
    if (dt1==dt2) {return 0;}
    if (dt1<dt2) {return -1;}
    return 1;
  },
  
  shaker_sort: function(list, comp_func) {
    // A stable sort function to allow multi-level sorting of data
    // see: http://en.wikipedia.org/wiki/Cocktail_sort
    // thanks to Joseph Nahmias
    var b = 0;
    var t = list.length - 1;
    var swap = true;

    while(swap) {
        swap = false;
        for(var i = b; i < t; ++i) {
            if ( comp_func(list[i], list[i+1]) > 0 ) {
                var q = list[i]; list[i] = list[i+1]; list[i+1] = q;
                swap = true;
            }
        } // for
        t--;

        if (!swap) {break;}

        for(var i = t; i > b; --i) {
            if ( comp_func(list[i], list[i-1]) < 0 ) {
                var q = list[i]; list[i] = list[i-1]; list[i-1] = q;
                swap = true;
            }
        } // for
        b++;

    } // while(swap)
  }  


};
/* ******************************************************************
   Supporting functions: bundled here to avoid depending on a library
   ****************************************************************** */



// Dean Edwards/Matthias Miller/John Resig


// Dean's forEach: http://dean.edwards.name/base/forEach.js
/*
  forEach, version 1.0
  Copyright 2006, Dean Edwards
  License: http://www.opensource.org/licenses/mit-license.php
*/

// array-like enumeration
if (!Array.forEach) { // mozilla already supports this
  Array.forEach = function(array, block, context) {
    for (var i = 0; i < array.length; i++) {
      block.call(context, array[i], i, array);
    }
  };
}

// generic enumeration
Function.prototype.forEach = function(object, block, context) {
  for (var key in object) {
    if (typeof this.prototype[key] == "undefined") {
      block.call(context, object[key], key, object);
    }
  }
};

// character enumeration
String.forEach = function(string, block, context) {
  Array.forEach(string.split(""), function(chr, index) {
    block.call(context, chr, index, string);
  });
};

// globally resolve forEach enumeration
var forEach = function(object, block, context) {
  if (object) {
    var resolve = Object; // default
    if (object instanceof Function) {
      // functions have a "length" property
      resolve = Function;
    } else if (object.forEach instanceof Function) {
      // the object implements a custom forEach method so use that
      object.forEach(block, context);
      return;
    } else if (typeof object == "string") {
      // the object is a string
      resolve = String;
    } else if (typeof object.length == "number") {
      // the object is array-like
      resolve = Array;
    }
    resolve.forEach(object, block, context);
  }
};


if ('undefined' != typeof(window.addEvent)) {
    window.addEvent(window, 'load', sorttable.init);
} else {
    jQuery(function() {
      sorttable.init();
    });
}

//sorttable.init;

function reinitsort() {
  sorttable.reinit();
}
