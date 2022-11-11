<script>
(function()
{
    function noImmediatePropagation(e) {
        e.stopImmediatePropagation();
    }

	var r = document.getElementsByClassName('overflow_tooltip');
	for(var i = 0; i < r.length; i++) {
        r[i].addEventListener('mouseenter', function(e) {
            if (this.offsetWidth >= this.scrollWidth)
                e.stopImmediatePropagation();
        }, false);
        r[i].addEventListener('mousedown', noImmediatePropagation, false);
        r[i].addEventListener('focusin', noImmediatePropagation, false);
	}
})();
</script>
