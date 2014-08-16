/*
UserformScripts.JS
injects code into the Doc Ready of any admin Form

Specifically to account for actions such as Typeahead

**** Example *****

var bestPictures = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    prefetch: '../data/films/post_1960.json',
    remote: '../data/films/queries/%QUERY.json'
});
 
bestPictures.initialize();
 
$('.typeahead').typeahead(null, {
    name: 'best-pictures',
    displayKey: 'value',
    source: bestPictures.ttAdapter()
});   

*/