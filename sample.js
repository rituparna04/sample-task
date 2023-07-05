document.on('click', '.search', function () {
    if (this.classList.contains("inCheatSheet")) {
        let value = this.previousElementSibling.value;
        value = value.toLowerCase();
        console.log(value);
        let refID = document.getElementByClassName("cheatSheet-inner").data('ref');
        $('.cheatModal .cheatSheet-inner').remove();
        $.post('includes/calls/cheatSheet.php', ({ search: value, ref: refID }), function (data) {
            document.getElementByClassName("cheatModal").append(data);
        })
    } else {
        let value = this.previousElementSibling.value, filter = '', status = '', page = '';
        let search = '&search=' + value;
        document.querySelectorAll(".filter select").each(function () {
            if (this.data().type == 'filter') {
                filter = '&filter=' + this.value;
            }
            if (this.data().type == 'status') {
                let thisStatus = this.value == "New Quote" ? 'New+Quote' : this.value;
                status = '&status=' + thisStatus;
            }
        })
        const urlParams = new URLSearchParams(window.location.search);
        for (const [key, value] of urlParams.entries()) {
            if (key == 'page') {
                page = page == '' ? 'page=' + value : page;
            }
        }
        window.location.assign(`dashboard.php?${page}${search}${filter}${status}`);
    }
})// JavaScript Document