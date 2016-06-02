function onCheckItem()
{
    for (var i = 0; i < document.form.elements.length; ++i) {
        if (document.form.elements[i].type == 'checkbox') {
            if (document.form.all.checked == true)
                document.form.elements[i].checked = true;
            else
                document.form.elements[i].checked = false;
        }
    }
}