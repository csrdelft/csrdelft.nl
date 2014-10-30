/* DOKUWIKI:include_once vendor/jquery.textcomplete.js */
function linksuggest_escape(text){
    return jQuery('<div/>').text(text).html()
}
jQuery(function(){
    jQuery('#wiki__text').textcomplete([{
        match: /\[\[([\w\.:]*)$/,
        search: function (term, callback) {
            jQuery.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                {call:'plugin_linksuggest',
                    q:term,
                    ns:JSINFO['namespace'],
                    id:JSINFO['id'],
                },
                function (data) {
                    data=JSON.parse(data);
                    callback(jQuery.map(data.data,function(item){
                        var id = item.id;
                        
                        if(item.type === 'd')
                            id = id + ':';
                        
                        return {id:id,
                            ns:item.ns,
                            title:item.title,
                            type:item.type,
                            rootns:item.rootns
                            };
                    }));
                }
            );
        },
        template:function(item){ //dropdown list
            var image = '';
            var title = item.title?' ('+linksuggest_escape(item.title)+')':'';
            var value = item.id;
            
            if(item.rootns){ //page is in root namespace
                value = ':'+value;
            }
            if(item.type === 'd'){ //namespace
                image = 'ns.png';
            } else { //file
                image = 'page.png';
            }
            return '<img src="'+DOKU_BASE+'lib/images/'+image+'"> '+linksuggest_escape(value) + title;
        },
        index: 1,
        replace: function (item) { //returns what will be put to editor
            var id = item.id;
            if(item.ns === ':'){ //absolute link
                id  = item.ns + id;
            } else if (item.ns) { //relative link
                id = item.ns + ':' + id;
            }
            if(item.type === 'd'){ //namespace
                return '[[' + id;
            } else { //file
                return ['[[' + id ,'|'+(item.title?item.title:'') + ']]'];
            }
            
        },
        cache:true
    },{
        match: /\[\[([\w\.:]+#[\w\.:]*)$/, 
        index: 1,
        search: function (term, callback) {
            jQuery.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                {call:'plugin_linksuggest',
                    q:term,
                    ns:JSINFO['namespace'],
                    id:JSINFO['id'], 
                },
                function (data) {
                    data=JSON.parse(data);
                    callback(jQuery.map(data.data,function(item){
                        return {'link':data.link,'toc':item};
                    }));
                }
            );
        },
        template:function(item){ //dropdown list
            var toc = item.toc;
            var title = toc.title?' ('+linksuggest_escape(toc.title)+')':'';

            return linksuggest_escape(toc.hid) + title;
        },
        
        replace: function (item) { //returns what will be put to editor
            var link = item.link;
            var toc = item.toc;
            
            return '[[' + link + '#' + toc.hid;
        },
        cache:true
    }]);
});
