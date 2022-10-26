const lieuSelected = document.getElementById('sortie_lieu');
const ville = document.getElementById('ville');
const rue = document.getElementById('rue');
const codePostal = document.getElementById('codePostal');
const latitude = document.getElementById('latitude');
const longitude = document.getElementById('longitude');

document.addEventListener('DOMContentLoaded', () => {
    loadInformations();

    lieuSelected.addEventListener('change', () => {
        loadInformations();
    });
});

const loadInformations = async () => {
    await fetch(`{{ path('app_lieu') }}${lieuSelected.options[lieuSelected.selectedIndex].value}`).then(responses => {
        responses.json().then(place => {
            ville.textContent = place?.ville?.nom ? place.ville.nom : '...';
            rue.textContent = place?.rue ? place.rue : '...';
            codePostal.textContent = place?.ville?.codePostal ? place.ville.codePostal : '...';
            latitude.textContent = place.latitude ? place.latitude : '...';
            longitude.textContent = place.longitude ? place.longitude : '...';
        });
    });
};