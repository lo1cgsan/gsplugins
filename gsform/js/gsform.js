$(document).ready(function() {
		$('.gsftip').hover(
		function(){
			var id=$(this).attr('id')+'c';
			var tresc=$('#'+id).text();
			if (tresc.length<1) tresc=$('#'+id).val();
			if (tresc.length<1) tresc='Brak podpowiedzi.';
			$('#gsftip').html(tresc);
		},
		function(){
			$('#gsftip').html('&nbsp;');
	});

	$('.gsftip').mousemove(function(e){
		var topY = e.pageY+10;
		var topX = e.pageX+10;
		$('#tip').css({
			'top':topY+'px',
			'left':topX+'px'
		});
	});

	$('select.gsfban').change(function(){
		if ($(this).val() > 0) {
			var mypage="?id=gsform&gsform_block&catb="+$(this).val();
			load(mypage);
		}
	});

	$('select.gsfopt').change(function() {
		var val = $(this).val();
		var ta = $(this).next('textarea');
		if (val == 'select' || val == 'radio') { ta.removeClass('gsfhide'); }
		else { ta.addClass('gsfhide'); }
	});
	$('#gsfadd').click(function() {
		var tr = $('#gsftbadd').find('tr.gsfhide');
		tr.before(tr.clone(true).removeClass('gsfhide').addClass('sortable'));
		var td = tr.find('td:first');
		td.text(parseInt(td.text())+1);
	});
	$('.newchk').click(function(){
		if ($(this).is(':checked')) {
			var tr = $(this).closest('tr.sortable');
			var cel = tr.find('input.req');
			if (cel.val().length<3) {
				alert('Nazwa i etykieta pola powinny mieć min. 3 znaki.');
				$(this).removeAttr('checked');
			} else $(this).val(cel.val());
		}
	});
	$('.gsfdel').click(function() {
		$(this).parent().parent().next().each(function() {
			if ($(this).attr('class')!='gsfhide') {
				var td = $(this).find('td:first');
				td.text(parseInt(td.text()-1));
			}
		});
		$(this).parent().parent().remove();
		var nr = $('#gsftbadd tr').length-1;
		var td = $('#gsftbadd').find('tr.gsfhide').find('td:first');
		td.text(nr);
	});
	$('#addfrm1').submit(function() {
		var flaga = true;
		if ($('input[name=fname]').val().length < 3) {
			alert('Podaj nazwę szablonu formularza (min. 3 znaki)');
			return false;
		}
		if (isNaN(parseInt($('input[name=fentnr]').val())) || (parseInt($('input[name=fentnr]').val()) < 1)) {
			alert('Liczba wpisów musi być liczbą dodatnią.');
			return false;
		}
		var nr = $('#gsftbadd tr').length-2;
		if (nr < 1) {
			alert('Dodaj przynajmniej jedno pole.');
			return false;
		}
		$('input.req').each(function() {
			var trcl = $(this).parent().parent().attr('class');
			if (trcl == 'sortable' && $(this).val().length < 3) {
				flaga = false;
			}
		});
		if (!flaga) alert('Nazwa i etykieta pola powinny mieć min. 3 znaki.');
		return flaga;
	});
//-------- GSFBLOCK ---------	
	$('#addfrm2').submit(function() {
		var flaga = true;
		//var nr = $('#gsftbadd tr').length-2;
		//if (nr < 1) {
		//	alert('Dodaj przynajmniej jedno pole.');
		//	return false;
		//}
		$('input.req').each(function() {
			var trcl = $(this).parent().parent().attr('class');
			if (trcl == 'sortable' && $(this).val().length < 3) {
				flaga = false;
			}
		});
		if (!flaga) alert('Wpis powinien mieć min. 3 znaki.');
		return flaga;
	});
//--------
	
	$('#userForm').submit(function() {
		var flaga = true;
		var fldreq = new Array();
		var fldchecked = new Array();
		$('#gsfinfo').html('');
		$('.gsfreq').each(function() {
			var typ=$(this).prop('type');
			var name=$(this).prop('name');
			var label=$('label.'+name).text();
			//var label=$(this).parent().parent().find('td:first').text();
			var kom='';
			if (typ == 'text' || typ == 'password' || typ == 'textarea') {
				if ($(this).val().length<1 && kom=='') {
					$('#gsfinfo').html($('#gsfinfo').html()+'Pole '+label+' jest wymagane!<br />');
					flaga = false;
				}
			} else if (typ == 'radio' || typ == 'checkbox') {
				flaga=$(this).is(':checked');
				if (!flaga && ($.inArray(name,fldreq) < 0) && ($.inArray(name,fldchecked) < 0)) {
					//console.log('req:'+name);
					fldreq.push(name);
				} else if (flaga) {
					fldchecked.push(name);
					if ($.inArray(name,fldreq) > -1) fldreq.splice($.inArray(name,fldreq),1);
					//console.log('chk:'+name);
				}
			}
		});

		if (fldreq.length>0) {
			for (i=0; i<fldreq.length; i++) {
				console.log(fldreq[i]);
				$('#gsfinfo').html($('#gsfinfo').html()+'Pole '+fldreq[i]+' jest wymagane!<br />');
			}
			flaga=false;			
		} else flaga=true;
		return flaga;
	});
});
function load (page){ this.location=page; }
function setmod(id) {
	el=document.getElementById(id);
	if (el.checked) el.value=id;
	else el.value=null;
}
