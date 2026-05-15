@auth('parent')
@if(! auth('parent')->user()->is_verifie_phone)
<div id="phoneVerifyOverlay" style="
    position: fixed;
    inset: 0;
    z-index: 9998;
    background: transparent;
    pointer-events: all;
    cursor: not-allowed;
" aria-hidden="true"></div>

{{-- ── Bandeau d'alerte flottant ────────────────────────────────────── --}}
<div id="phoneVerifyBanner" style="
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 16px;
    padding: 14px 24px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.18);
    display: flex;
    align-items: center;
    gap: 14px;
    max-width: 520px;
    width: calc(100% - 32px);
    animation: slideUp 0.4s cubic-bezier(0.4,0,0.2,1);
">
    <i class="fas fa-mobile-alt fa-lg text-warning"></i>
    <div class="flex-grow-1">
        <strong style="color:#856404;">Vérifiez votre numéro de téléphone</strong>
        <div style="font-size:0.85rem;color:#856404;">Cliquez sur les boutons pour commencer.</div>
    </div>
    <button onclick="document.getElementById('phoneVerifyModal').style.display='flex'"
        class="btn btn-warning btn-sm fw-bold" style="white-space:nowrap;">
        <i class="fas fa-shield-alt me-1"></i> Vérifier
    </button>
</div>
@endif
@endauth

{{-- ── Modal principal ────────────────────────────────────────────────── --}}
@auth('parent')
@if(! auth('parent')->user()->is_verifie_phone)
<div id="phoneVerifyModal" style="
    display: none;
    position: fixed;
    inset: 0;
    z-index: 10000;
    background: rgba(30,40,50,0.65);
    backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
    padding: 16px;
">
    <div style="
        background: #fff;
        border-radius: 24px;
        width: 100%;
        max-width: 460px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        overflow: hidden;
        animation: modalIn 0.3s cubic-bezier(0.4,0,0.2,1);
    ">
        {{-- En-tête --}}
        <div style="background: linear-gradient(135deg,#2E7D32,#4CAF50); padding:28px 28px 20px; text-align:center;">
            <div style="width:64px;height:64px;background:rgba(255,255,255,0.2);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <i class="fas fa-mobile-alt fa-2x text-white"></i>
            </div>
            <h5 style="color:#fff;margin:0;font-weight:700;font-size:1.2rem;">Vérification du numéro</h5>
            <p style="color:rgba(255,255,255,0.8);margin:4px 0 0;font-size:0.88rem;">Sécurisez votre compte en quelques secondes</p>
        </div>

        {{-- Corps --}}
        <div style="padding:28px;">

            {{-- ÉTAPE 1 : Envoi OTP --}}
            <div id="step1">
                <p style="color:#546E7A;font-size:0.92rem;margin-bottom:18px;text-align:center;">
                    Nous allons envoyer un code à 5 chiffres par <strong>WhatsApp</strong> sur votre numéro.
                </p>

                <div style="margin-bottom:20px;">
                    <label style="font-size:0.82rem;color:#546E7A;font-weight:600;margin-bottom:6px;display:block;">
                        <i class="fas fa-phone me-1 text-success"></i> Votre numéro WhatsApp
                    </label>
                    <input type="text"
                        id="displayPhone"
                        value="{{ auth('parent')->user() ? ('+229' . ltrim(auth('parent')->user()->phone, '01')) : '' }}"
                        readonly
                        style="
                            width:100%;
                            padding:12px 16px;
                            border:2px solid #e0e0e0;
                            border-radius:12px;
                            background:#f8f9fa;
                            color:#263238;
                            font-size:1rem;
                            font-weight:600;
                            letter-spacing:1px;
                            cursor:not-allowed;
                        ">
                    <small style="color:#90A4AE;font-size:0.78rem;margin-top:4px;display:block;">
                        <i class="fas fa-lock me-1"></i> Numéro non modifiable
                    </small>
                </div>

                <button id="btnSendOtp" onclick="sendOtp()" class="btn btn-success w-100 fw-bold" style="border-radius:12px;padding:13px;">
                    <i class="fas fa-paper-plane me-2"></i> Envoyer le code sur WhatsApp
                </button>
            </div>

            {{-- ÉTAPE 2 : Saisie OTP --}}
            <div id="step2" style="display:none;">
                <div style="text-align:center;margin-bottom:20px;">
                    <div style="width:56px;height:56px;background:#E8F5E9;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:10px;">
                        <i class="fab fa-whatsapp fa-2x text-success"></i>
                    </div>
                    <p style="color:#546E7A;font-size:0.9rem;margin:0;">
                        Code envoyé sur <strong id="sentToNumber"></strong><br>
                        <small style="color:#90A4AE;">Valable 5 minutes</small>
                    </p>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="font-size:0.82rem;color:#546E7A;font-weight:600;margin-bottom:8px;display:block;">
                        Entrez le code reçu
                    </label>
                    <input type="text"
                        id="otpInput"
                        maxlength="5"
                        inputmode="numeric"
                        placeholder="• • • • •"
                        oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                        style="
                            width:100%;
                            padding:16px;
                            border:2px solid #e0e0e0;
                            border-radius:12px;
                            font-size:2rem;
                            font-weight:700;
                            text-align:center;
                            letter-spacing:12px;
                            color:#2E7D32;
                            transition: border-color 0.2s;
                        "
                        onfocus="this.style.borderColor='#4CAF50'"
                        onblur="this.style.borderColor='#e0e0e0'">
                    <div id="otpError" style="color:#C62828;font-size:0.82rem;margin-top:6px;display:none;"></div>
                </div>

                <button id="btnVerifyOtp" onclick="verifyOtp()" class="btn btn-success w-100 fw-bold mb-3" style="border-radius:12px;padding:13px;">
                    <i class="fas fa-check-circle me-2"></i> Vérifier mon numéro
                </button>

                <div style="text-align:center;">
                    <small style="color:#90A4AE;">Vous n'avez pas reçu le code ?</small><br>
                    <button id="btnResend" onclick="resendOtp()" disabled style="
                        background:none;border:none;color:#2E7D32;font-weight:600;cursor:pointer;font-size:0.85rem;padding:4px 0;
                    ">
                        <i class="fas fa-redo me-1"></i> Renvoyer le code
                        <span id="countdownDisplay"></span>
                    </button>
                </div>
            </div>

            {{-- ÉTAPE 3 : Nouveau mot de passe --}}
            <div id="step3" style="display:none;">
                <div style="text-align:center;margin-bottom:20px;">
                    <div style="width:56px;height:56px;background:#E8F5E9;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:10px;">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <p style="color:#2E7D32;font-weight:700;margin:0;">Numéro vérifié !</p>
                    <small style="color:#546E7A;">Définissez maintenant votre nouveau mot de passe</small>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="font-size:0.82rem;color:#546E7A;font-weight:600;margin-bottom:6px;display:block;">
                        <i class="fas fa-lock me-1 text-success"></i> Nouveau mot de passe
                    </label>
                    <div style="position:relative;">
                        <input type="password"
                            id="newPassword"
                            placeholder="Minimum 8 caractères"
                            style="
                                width:100%;padding:12px 42px 12px 16px;
                                border:2px solid #e0e0e0;border-radius:12px;
                                font-size:0.95rem;transition:border-color 0.2s;
                            "
                            onfocus="this.style.borderColor='#4CAF50'"
                            onblur="this.style.borderColor='#e0e0e0'">
                        <i onclick="togglePwd('newPassword',this)" class="fas fa-eye" style="
                            position:absolute;right:14px;top:50%;transform:translateY(-50%);
                            color:#90A4AE;cursor:pointer;
                        "></i>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="font-size:0.82rem;color:#546E7A;font-weight:600;margin-bottom:6px;display:block;">
                        <i class="fas fa-lock me-1 text-success"></i> Confirmer le mot de passe
                    </label>
                    <div style="position:relative;">
                        <input type="password"
                            id="confirmPassword"
                            placeholder="Répétez le mot de passe"
                            style="
                                width:100%;padding:12px 42px 12px 16px;
                                border:2px solid #e0e0e0;border-radius:12px;
                                font-size:0.95rem;transition:border-color 0.2s;
                            "
                            onfocus="this.style.borderColor='#4CAF50'"
                            onblur="this.style.borderColor='#e0e0e0'">
                        <i onclick="togglePwd('confirmPassword',this)" class="fas fa-eye" style="
                            position:absolute;right:14px;top:50%;transform:translateY(-50%);
                            color:#90A4AE;cursor:pointer;
                        "></i>
                    </div>
                    <div id="pwdError" style="color:#C62828;font-size:0.82rem;margin-top:6px;display:none;"></div>
                </div>

                <button id="btnChangePwd" onclick="changePassword()" class="btn btn-success w-100 fw-bold" style="border-radius:12px;padding:13px;">
                    <i class="fas fa-save me-2"></i> Enregistrer mon mot de passe
                </button>
            </div>

        </div>{{-- fin corps --}}
    </div>
</div>

{{-- ── Styles ──────────────────────────────────────────────────────────── --}}
<style>
@keyframes slideUp {
    from { transform: translateX(-50%) translateY(30px); opacity:0; }
    to   { transform: translateX(-50%) translateY(0);    opacity:1; }
}
@keyframes modalIn {
    from { transform: scale(0.9); opacity:0; }
    to   { transform: scale(1);   opacity:1; }
}

/* Bloquer le clic sur tous les éléments interactifs sauf le modal */
body.phone-not-verified a:not([data-allow]),
body.phone-not-verified button:not([data-allow]),
body.phone-not-verified .nav-link:not([data-allow]) {
    pointer-events: none !important;
    opacity: 0.55 !important;
    cursor: not-allowed !important;
    position: relative;
}
body.phone-not-verified a:not([data-allow])::after,
body.phone-not-verified button:not([data-allow])::after {
    content: '';
    position: absolute;
    inset: 0;
    cursor: not-allowed;
}
</style>

{{-- ── Script ──────────────────────────────────────────────────────────── --}}
<script>
(function () {
    // Marquer le body pour le CSS de blocage
    document.body.classList.add('phone-not-verified');

    // Autoriser le bouton "Vérifier" du bandeau et les éléments du modal
    document.querySelectorAll('#phoneVerifyBanner button, #phoneVerifyModal button, #phoneVerifyModal input')
        .forEach(el => el.setAttribute('data-allow', '1'));

    // Clic sur le bandeau/overlay → ouvrir modal
    document.getElementById('phoneVerifyOverlay').addEventListener('click', function () {
        document.getElementById('phoneVerifyModal').style.display = 'flex';
    });

    let countdownInterval = null;

    // ── Afficher le modal immédiatement si jamais l'overlay bloque ──
    document.querySelectorAll('a:not([data-allow]), button:not([data-allow])').forEach(el => {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('phoneVerifyModal').style.display = 'flex';
        }, true);
    });

    // ── Utilitaire : bouton en chargement ──
    function setLoading(btnId, loading, label) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.disabled = loading;
        btn.innerHTML = loading
            ? '<span class="spinner-border spinner-border-sm me-2"></span>Patientez...'
            : label;
    }

    // ── Afficher/cacher erreur OTP ──
    function showOtpError(msg) {
        const el = document.getElementById('otpError');
        el.textContent = msg;
        el.style.display = msg ? 'block' : 'none';
        const inp = document.getElementById('otpInput');
        inp.style.borderColor = msg ? '#C62828' : '#e0e0e0';
    }

    function showPwdError(msg) {
        const el = document.getElementById('pwdError');
        el.textContent = msg;
        el.style.display = msg ? 'block' : 'none';
    }

    // ── Décompte re-envoi ──
    function startCountdown(seconds) {
        const btn = document.getElementById('btnResend');
        const disp = document.getElementById('countdownDisplay');
        btn.disabled = true;
        let remaining = seconds;

        clearInterval(countdownInterval);
        countdownInterval = setInterval(function () {
            remaining--;
            disp.textContent = ' (' + remaining + 's)';
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                btn.disabled = false;
                disp.textContent = '';
            }
        }, 1000);
    }

    // ── ÉTAPE 1 : Envoyer OTP ──
    window.sendOtp = function () {
        setLoading('btnSendOtp', true, '<i class="fas fa-paper-plane me-2"></i> Envoyer le code sur WhatsApp');

        fetch('{{ route("parent.phone.send-otp") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({})
        })
        .then(async r => {
            const text = await r.text();
            let data;
            try { data = JSON.parse(text); } catch(e) {
                // Le serveur a retourné du HTML (ex: erreur 500 Laravel)
                console.error('[OTP send] Réponse non-JSON :', text);
                throw new Error('Réponse serveur invalide (HTTP ' + r.status + ')');
            }
            if (!r.ok && !data.message) throw new Error('Erreur HTTP ' + r.status);
            return data;
        })
        .then(data => {
            setLoading('btnSendOtp', false, '<i class="fas fa-paper-plane me-2"></i> Envoyer le code sur WhatsApp');

            if (data.success) {
                // Passer à l'étape 2
                document.getElementById('step1').style.display = 'none';
                document.getElementById('step2').style.display = 'block';
                document.getElementById('sentToNumber').textContent = data.phone_display || '';
                startCountdown(60);
                document.getElementById('otpInput').focus();
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Attention',
                    text: data.message || 'Une erreur est survenue.',
                    confirmButtonColor: '#2E7D32',
                });
            }
        })
        .catch(err => {
            setLoading('btnSendOtp', false, '<i class="fas fa-paper-plane me-2"></i> Envoyer le code sur WhatsApp');
            Swal.fire({ icon:'error', title:'Erreur', text: err.message || 'Vérifiez votre connexion internet.', confirmButtonColor:'#2E7D32' });
        });
    };

    // ── Re-envoyer OTP ──
    window.resendOtp = function () {
        window.sendOtp();
        // sendOtp gère lui-même le retour à step1 si nécessaire — ici on reste step2
        // On réinitialise juste l'input
        document.getElementById('otpInput').value = '';
        showOtpError('');
    };

    // ── ÉTAPE 2 : Vérifier OTP ──
    window.verifyOtp = function () {
        const code = document.getElementById('otpInput').value.trim();
        if (code.length !== 5) { showOtpError('Entrez les 5 chiffres du code.'); return; }

        setLoading('btnVerifyOtp', true, '<i class="fas fa-check-circle me-2"></i> Vérifier mon numéro');

        fetch('{{ route("parent.phone.verify-otp") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ otp: code })
        })
        .then(async r => {
            const text = await r.text();
            let data;
            try { data = JSON.parse(text); } catch(e) {
                console.error('[OTP verify] Réponse non-JSON :', text);
                throw new Error('Réponse serveur invalide (HTTP ' + r.status + ')');
            }
            return data;
        })
        .then(data => {
            setLoading('btnVerifyOtp', false, '<i class="fas fa-check-circle me-2"></i> Vérifier mon numéro');

            if (data.success) {
                clearInterval(countdownInterval);
                document.getElementById('step2').style.display = 'none';
                document.getElementById('step3').style.display = 'block';
                document.getElementById('newPassword').focus();
            } else {
                if (data.expired) {
                    showOtpError('');
                    document.getElementById('step2').style.display = 'none';
                    document.getElementById('step1').style.display = 'block';
                    Swal.fire({ icon:'warning', title:'Code expiré', text:data.message, confirmButtonColor:'#2E7D32' });
                } else {
                    showOtpError(data.message || 'Code incorrect.');
                }
            }
        })
        .catch(err => {
            setLoading('btnVerifyOtp', false, '<i class="fas fa-check-circle me-2"></i> Vérifier mon numéro');
            Swal.fire({ icon:'error', title:'Erreur', text: err.message || 'Vérifiez votre connexion internet.', confirmButtonColor:'#2E7D32' });
        });
    };

    // ── ÉTAPE 3 : Changer le mot de passe ──
    window.changePassword = function () {
        const pwd  = document.getElementById('newPassword').value;
        const conf = document.getElementById('confirmPassword').value;

        if (pwd.length < 8) { showPwdError('Le mot de passe doit comporter au moins 8 caractères.'); return; }
        if (pwd !== conf)   { showPwdError('Les deux mots de passe ne correspondent pas.'); return; }
        showPwdError('');

        setLoading('btnChangePwd', true, '<i class="fas fa-save me-2"></i> Enregistrer mon mot de passe');

        fetch('{{ route("parent.phone.change-password") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ password: pwd, password_confirmation: conf })
        })
        .then(async r => {
            const text = await r.text();
            let data;
            try { data = JSON.parse(text); } catch(e) {
                console.error('[change-pwd] Réponse non-JSON :', text);
                throw new Error('Réponse serveur invalide (HTTP ' + r.status + ')');
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                // Fermer le modal et tout débloquer
                document.getElementById('phoneVerifyModal').style.display = 'none';
                document.getElementById('phoneVerifyOverlay').remove();
                document.getElementById('phoneVerifyBanner').remove();
                document.body.classList.remove('phone-not-verified');

                Swal.fire({
                    icon: 'success',
                    title: 'Compte vérifié !',
                    html: `<p>Votre numéro a été vérifié et votre mot de passe mis à jour.<br><br>
                           <strong>Utilisez désormais ce mot de passe pour vos prochaines connexions :</strong><br>
                           <code style="font-size:1.3rem;background:#f1f8e9;padding:8px 16px;border-radius:8px;display:inline-block;margin-top:8px;letter-spacing:2px;color:#2E7D32;">${data.new_password}</code><br><br>
                           <small class="text-muted">Un récapitulatif vous a été envoyé par WhatsApp.</small></p>`,
                    confirmButtonText: 'Parfait, merci !',
                    confirmButtonColor: '#2E7D32',
                    allowOutsideClick: false,
                });
            } else {
                setLoading('btnChangePwd', false, '<i class="fas fa-save me-2"></i> Enregistrer mon mot de passe');
                showPwdError(data.message || 'Une erreur est survenue.');
            }
        })
        .catch(err => {
            setLoading('btnChangePwd', false, '<i class="fas fa-save me-2"></i> Enregistrer mon mot de passe');
            Swal.fire({ icon:'error', title:'Erreur', text: err.message || 'Vérifiez votre connexion internet.', confirmButtonColor:'#2E7D32' });
        });
    };

    // ── Afficher/masquer mot de passe ──
    window.togglePwd = function (inputId, icon) {
        const inp = document.getElementById(inputId);
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            inp.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    };


    document.addEventListener('click', function (e) {
        const body = document.body;
        if (!body.classList.contains('phone-not-verified')) return;

        // Si le clic est dans le modal ou le bandeau → laisser passer
        const modal  = document.getElementById('phoneVerifyModal');
        const banner = document.getElementById('phoneVerifyBanner');
        if ((modal  && modal.contains(e.target)) ||
            (banner && banner.contains(e.target))) return;

        // Sinon bloquer et ouvrir le modal
        e.preventDefault();
        e.stopImmediatePropagation();
        modal.style.display = 'flex';
    }, true);

})();
</script>

@endif
@endauth