document.addEventListener("DOMContentLoaded", function () {
    const fondateur = "Nom Prénom";
    const texte = `
    <p>L'École CPEG MARIE-ALAIN a été fondée par <strong>${fondateur}</strong>, avec la vision de créer un environnement éducatif moderne, inclusif et stimulant pour tous les élèves.</p>
    <p>Depuis sa création, l’école s’engage à offrir une formation de qualité, centrée sur le développement intellectuel, social et émotionnel des enfants. 
    Grâce à une équipe pédagogique passionnée et des infrastructures adaptées, nous accompagnons chaque élève vers la réussite.</p>
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
