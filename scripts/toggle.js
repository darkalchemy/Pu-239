function toggle_search() {
    document.getElementById('simple').classList.toggle('hidden');
    document.getElementById('advanced').classList.toggle('hidden');
    if (document.getElementById('simple').classList.contains('hidden')) {
        let text = document.getElementById('search_sim').value;
        document.getElementById('search_sim').value = '';
        document.getElementById('search_adv').value = text;
    } else {
        let text = document.getElementById('search_adv').value;
        document.getElementById('search_adv').value = '';
        document.getElementById('search_sim').value = text;
    }
}