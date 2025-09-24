document.addEventListener("DOMContentLoaded", function () {
    const fondateur = "Nom Prénom";
    const texte = `
    <p>Depuis sa création en octobre 2007, le Complexe scolaire Marie-Alain s’impose comme un établissement de référence à Aitchédji Abomey-Calavi, en plaçant le plaisir d’apprendre et l’épanouissement de l’enfant au centre de sa mission éducative.
    De la Maternelle au Lycée, nous accueillons des classes à effectif raisonné (20 à 25 élèves) afin de garantir un suivi personnalisé et une proximité propice à la réussite de chacun.</p>
    <p>Notre engagement va bien au-delà de l’acquisition des savoirs. Nous œuvrons chaque jour à développer chez nos élèves :
     l’excellence académique, à travers des enseignements rigoureux et innovants ;
    la curiosité intellectuelle, en stimulant l’esprit critique, la créativité et la soif de découverte ;
    les valeurs humaines, telles que le respect, l’intégrité, la solidarité et le sens du devoir citoyen.</p>
  `;
    document.getElementById("ecole-description").innerHTML = texte;
});

const toggleBtn = document.getElementById("toggleBtn");
const moreCourses = document.getElementById("moreCourses");

toggleBtn.addEventListener("click", () => {
    const isVisible = moreCourses.classList.contains("show");

    // Attendre la fin de l'animation collapse (~350ms)
    setTimeout(() => {
        const nowVisible = moreCourses.classList.contains("show");
        toggleBtn.textContent = nowVisible ? "Voir moins" : "Voir plus";
        toggleBtn.classList.toggle("btn-vp", !nowVisible);
        toggleBtn.classList.toggle("btn-vm", nowVisible);
    }, 400);
});

const items = document.querySelectorAll(".timeline-item");

const observer = new IntersectionObserver(
    (entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
            }
        });
    },
    { threshold: 0.2 }
);

items.forEach((item) => observer.observe(item));
