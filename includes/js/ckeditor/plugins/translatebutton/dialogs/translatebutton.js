// Translate Button definition.

var thislcd = $('input[name="thislcd"]').val();
var lcd = $('input[name="langcd"]').val();
var notyid = noty({
    text: lcd + ' >> ' + thislcd, type: 'confirm', layout: 'center',
    buttons: [
        {addClass: '', text: 'Ok', onClick: function(notyid) {
            $('#editarea').translate(lcd, thislcd, {subject:true} );
            notyid.close();
        }},
        {addClass: '', text: 'Cancel', onClick: function(notyid) {
            notyid.close();
        }}
    ]
});


