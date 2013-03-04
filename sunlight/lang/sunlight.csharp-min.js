(function(a,e){if(a===e||a.registerLanguage===e){throw"Include sunlight.js before including language files";}var d=["int","bool","double","float","char","byte","sbyte","uint","long","ulong","char","decimal","short","ushort"],c=d.concat(["in","out","string","object"]);function b(g){var f=/^T([A-Z0-9]\w*)?$/;return function(h){return !f.test(h.tokens[h.index].value)&&g(h);};}a.registerLanguage("csharp",{keywords:d.concat(["extern alias","public","private","protected","internal","static","sealed","abstract","partial","virtual","override","new","implicit","explicit","extern","override","operator","const","readonly","volatile","class","interface","enum","struct","event","delegate","null","true","false","string","object","void","for","foreach","do","while","fixed","unchecked","using","lock","namespace","checked","unsafe","if","else","try","catch","finally","break","continue","goto","case","throw","return","switch","yield return","yield break","in","out","ref","params","as","is","typeof","this","sizeof","stackalloc","var","default","from","select","where","groupby","orderby"]),customParseRules:[function(g){var k="xmlDocCommentMeta",j="xmlDocCommentContent",i,f,h={line:0,column:0,value:"",name:null};if(g.reader.current()!=="/"||g.reader.peek(2)!=="//"){return null;}i=[g.createToken(k,"///",g.reader.getLine(),g.reader.getColumn())];g.reader.read(2);while((f=g.reader.peek())!==g.reader.EOF){if(f==="<"&&h.name!==k){if(h.value!==""){i.push(g.createToken(h.name,h.value,h.line,h.column));}h.line=g.reader.getLine();h.column=g.reader.getColumn();h.name=k;h.value=g.reader.read();continue;}if(f===">"&&h.name===k){h.value+=g.reader.read();i.push(g.createToken(h.name,h.value,h.line,h.column));h.name=null;h.value="";continue;}if(f==="\n"){break;}if(h.name===null){h.name=j;h.line=g.reader.getLine();h.column=g.reader.getColumn();}h.value+=g.reader.read();}if(h.name===j){i.push(g.createToken(h.name,h.value,h.line,h.column));}return i.length>0?i:null;},function(h){var m,k,g,j=false,f=h.reader.getLine(),i=h.reader.getColumn(),l;if(!/^(get|set)\b/.test(h.reader.current()+h.reader.peek(3))){return null;}m=a.util.createProceduralRule(h.count()-1,-1,[{token:"punctuation",values:["}","{",";"]},a.util.whitespace,{token:"keyword",values:["public","private","protected","internal"],optional:true}]);if(!m(h.getAllTokens())){return null;}k="get".length;g=h.reader.peek(k);while(g.length===k){if(!/\s$/.test(g)){if(!/[\{;]$/.test(g)){return null;}j=true;break;}g=h.reader.peek(++k);}if(!j){return null;}l=h.reader.current()+h.reader.read(2);return h.createToken("keyword",l,f,i);},function(f){var k,m,h,j,l,g,o=f.reader.getLine(),i=f.reader.getColumn(),n;if(!/^value\b/.test(f.reader.current()+f.reader.peek(5))){return null;}k="value".length;m=f.reader.peek(k);while(m.length===k){if(!/\s$/.test(m)){n=f.reader.peek(k+1);if(m.charAt(m.length-1)==="="&&n.charAt(n.length-1)!=="="){return null;}h=true;break;}m=f.reader.peek(++k);}if(!h){return null;}l=f.count()-1;g=[0,0];tokenLoop:while((j=f.token(l--))!==e){if(j.name==="punctuation"){if(j.value==="{"){g[0]++;}else{if(j.value==="}"){g[1]++;}}}else{if(j.name==="keyword"){switch(j.value){case"set":break tokenLoop;case"class":case"public":case"private":case"protected":case"internal":return null;}}}}if(j===e){return null;}if(g[1]>=g[0]){return null;}f.reader.read(4);return f.createToken("keyword","value",o,i);}],scopes:{string:[['"','"',a.util.escapeSequences.concat(['\\"'])],['@"','"',['""']]],"char":[["'","'",["\\'","\\\\"]]],comment:[["//","\n",null,true],["/*","*/"]],pragma:[["#","\n",null,true]]},identFirstLetter:/[A-Za-z_@]/,identAfterFirstLetter:/\w/,namedIdentRules:{custom:[b(function(j){var h=j.index,i,g=false,f;while((i=j.tokens[--h])!==e){if(i.name==="punctuation"&&i.value==="{"){return false;}if(i.name==="keyword"&&i.value==="case"){return false;}if(i.name==="keyword"&&(i.value==="class"||i.value==="where")){f=j.tokens[h+1].name==="default"?j.tokens[h+2]:j.tokens[h+1];if(f.name==="punctuation"&&f.value===","){continue;}break;}if(i.name==="operator"&&i.value===":"){g=true;}}if(!g){return false;}return true;}),b(function(j){var h=j.index,i,g=false,f=[0,0];while((i=j.tokens[--h])!==e){if(i.name==="keyword"&&i.value==="class"){return false;}if(i.name==="operator"){switch(i.value){case"<":case"<<":f[0]+=i.value.length;continue;case">":case">>":if(f[0]===0){return false;}f[1]+=i.value.length;continue;}break;}if((i.name==="keyword"&&a.util.contains(c,i.value))||i.name==="default"||(i.name==="punctuation"&&i.value===",")){continue;}if(i.name==="ident"){g=true;continue;}break;}if(!g||f[0]===0){return false;}h=j.index;while((i=j.tokens[++h])!==e){if(i.name==="operator"&&(i.value===">"||i.value===">>")){return true;}if((i.name==="keyword"&&a.util.contains(c,i.value))||(i.name==="operator"&&a.util.contains(["<","<<",">",">>"],i.value))||(i.name==="punctuation"&&i.value===",")||i.name==="ident"||i.name==="default"){continue;}return false;}return false;}),b(function(i){var h=a.util.getPreviousNonWsToken(i.tokens,i.index),g,f;if(h!==e){if(h.name==="ident"||(h.name==="keyword"&&a.util.contains(d.concat(["string","object","void"]),h.value))||(h.name==="operator"&&h.value===".")){return false;}}h=a.util.getNextNonWsToken(i.tokens,i.index);if(!h||h.name!=="operator"||h.value!=="<"){return false;}g=i.index;f=[0,0];while((h=i.tokens[++g])!==e){if(h.name==="operator"){switch(h.value){case"<":f[0]++;break;case"<<":f[0]+=2;break;case">":f[1]++;break;case">>":f[1]+=2;break;default:return false;}if(f[0]===f[1]){break;}continue;}if(h.name==="default"||h.name==="ident"||(h.name==="keyword"&&a.util.contains(c,h.value))||(h.name==="punctuation"&&h.value===",")){continue;}return false;}if(f[0]!==f[1]){return false;}h=i.tokens[++g];if(!h||(h.name!=="default"&&h.name!=="ident")){return false;}if(h.name==="default"){h=i.tokens[++g];if(!h||h.name!=="ident"){return false;}}return true;}),function(g){var h=a.util.getPreviousNonWsToken(g.tokens,g.index),f;if(!h||h.name!=="keyword"||h.value!=="using"){return false;}f=a.util.getNextNonWsToken(g.tokens,g.index);if(!f||f.name!=="operator"||f.value!=="="){return false;}return true;},b(function(j){var i=a.util.getNextNonWsToken(j.tokens,j.index),g,f,k=false,h;if(i&&i.name==="operator"&&(i.value==="="||i.value===".")){return false;}g=j.index;f=[0,0];k=false;while((i=j.tokens[--g])!==e){if(i.name==="punctuation"){if(i.value==="["){f[0]++;continue;}if(i.value==="]"){f[1]++;continue;}if(i.value===","){k=true;}if(i.value==="{"||i.value==="}"||i.value===";"){break;}}}if(f[0]===0||f[0]===f[1]){return false;}g=j.index;h=-1;while((i=j.tokens[++g])!==e){if(i.name==="punctuation"){if(i.value==="["){f[0]++;continue;}if(i.value==="]"){h=g;f[1]++;continue;}if(i.value==="{"||i.value==="}"||i.value===";"){break;}}}if(h<0||f[0]!==f[1]){return false;}i=a.util.getNextNonWsToken(j.tokens,h);if(i&&(i.name==="keyword"||i.name==="ident")){return true;}return false;}),b(function(i){var f=a.util.getNextNonWsToken(i.tokens,i.index),h,g,j;if(f&&f.name==="operator"&&f.value==="."){return false;}g=i.index;j=i.tokens[g];while((h=i.tokens[--g])!==e){if(h.name==="keyword"&&(h.value==="new"||h.value==="is")){return true;}if(h.name==="default"){continue;}if(h.name==="ident"){if(j&&j.name==="ident"){return false;}j=h;continue;}if(h.name==="operator"&&h.value==="."){if(j&&j.name!=="ident"){return false;}j=h;continue;}break;}return false;}),function(){var f=[[a.util.whitespace,{token:"punctuation",values:[")"]},a.util.whitespace,{token:"ident"}],[a.util.whitespace,{token:"punctuation",values:[")"]},a.util.whitespace,{token:"keyword",values:["this"]}]];return b(function(j){var i,h,k,g=function(m){for(var l=0;l<f.length;l++){if(a.util.createProceduralRule(j.index+1,1,f[l],false)(m)){return true;}}return false;}(j.tokens);if(!g){return false;}h=j.index;while(i=j.tokens[--h]){if(i.name==="punctuation"&&i.value==="("){k=a.util.getPreviousNonWsToken(j.tokens,h);if(k&&k.name==="keyword"){return false;}return true;}}return false;});}(),function(i){var f=a.util.getNextNonWsToken(i.tokens,i.index),h,g;if(!f||f.name!=="punctuation"||f.value!==";"){return false;}g=i.index;while(h=i.tokens[--g]){if(h.name!=="ident"&&h.name!=="default"&&(h.name!=="operator"||h.value!==".")){if(h.name!=="operator"||h.value!=="="){return false;}return a.util.createProceduralRule(g-1,-1,[{token:"keyword",values:["using"]},{token:"default"},{token:"ident"},a.util.whitespace])(i.tokens);}}return false;},b(function(h){var g,j=[[{token:"keyword",values:["class","interface","event","struct","enum","delegate","public","private","protected","internal","static","virtual","sealed","params"]},a.util.whitespace],[{token:"keyword",values:["typeof","default"]},a.util.whitespace,{token:"punctuation",values:["("]},a.util.whitespace],[{token:"keyword",values:["as"]},a.util.whitespace]],f=[[a.util.whitespace,{token:"punctuation",values:["["]},a.util.whitespace,{token:"punctuation",values:["]"]}],[{token:"default"},{token:"ident"}]];for(g=0;g<j.length;g++){if(a.util.createProceduralRule(h.index-1,-1,j[g],false)(h.tokens)){return true;}}for(g=0;g<f.length;g++){if(a.util.createProceduralRule(h.index+1,1,f[g],false)(h.tokens)){return true;}}return false;})]},operators:["++","+=","+","--","-=","-","*=","*","/=","/","%=","%","&&","||","|=","|","&=","&","^=","^",">>=",">>","<<=","<<","<=","<",">=",">","==","!=","!","~","??","?","::",":",".","=>","="]});}(this["Sunlight"]));