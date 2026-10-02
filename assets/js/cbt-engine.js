// assets/js/cbt-engine.js - Engine CBT Pusmendik ANBK dengan Kiosk Lockdown & Token CMD Pengawas

class CBTEngine {
    constructor(config) {
        this.token = config.token;
        this.currentNomor = 1;
        this.totalSoal = 10;
        this.timerSeconds = 0;
        this.timerInterval = null;
        this.paletteData = [];
        this.currentSoalData = null;
        this.isLocked = false;
        this.isSubmitting = false;

        this.init();
    }

    init() {
        this.setupKioskLockdown();
        this.setupEventListeners();
        this.loadQuestion(this.currentNomor);
    }

    setupKioskLockdown() {
        // 1. Mencegah Navigasi Tombol Back di Browser
        history.pushState(null, null, location.href);
        window.addEventListener('popstate', () => {
            history.pushState(null, null, location.href);
        });

        // 2. Mencegah Reload / Penutupan Tab Tidak Sengaja
        window.addEventListener('beforeunload', (e) => {
            if (!this.isSubmitting) {
                e.preventDefault();
                e.returnValue = 'Ujian sedang berjalan! Jangan tinggalkan lembar ujian.';
            }
        });

        // 3. Blokir Klik Kanan
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            return false;
        });

        // 4. Blokir Tombol Pintas DevTools & Navigasi
        document.addEventListener('keydown', (e) => {
            if (
                e.key === 'F12' ||
                (e.ctrlKey && e.shiftKey && ['I', 'i', 'J', 'j', 'C', 'c'].includes(e.key)) ||
                (e.ctrlKey && ['u', 'U', 's', 'S'].includes(e.key)) ||
                (e.altKey && e.key === 'ArrowLeft')
            ) {
                e.preventDefault();
                return false;
            }
        });

        // 5. Deteksi Berpindah Tab atau Membuka Aplikasi Lain
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && !this.isSubmitting && !this.isLocked) {
                const prompt = document.getElementById('startFullscreenPrompt');
                if (!prompt || prompt.classList.contains('hidden')) {
                    this.triggerLockout('Berpindah tab atau membuka aplikasi lain');
                }
            }
        });

        // 6. Deteksi Keluar Layar Penuh (Fullscreen)
        const onFullscreenChange = () => {
            const isFull = !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement);
            const prompt = document.getElementById('startFullscreenPrompt');

            // Jika keluar fullscreen dan modal awal sudah ditutup
            if (!isFull && !this.isSubmitting && !this.isLocked && (!prompt || prompt.classList.contains('hidden'))) {
                this.triggerLockout('Keluar dari Mode Layar Penuh (Fullscreen)');
            }
        };

        document.addEventListener('fullscreenchange', onFullscreenChange);
        document.addEventListener('webkitfullscreenchange', onFullscreenChange);

        // 7. Tombol Awal Masuk Fullscreen
        const btnStart = document.getElementById('btnStartExamFullscreen');
        if (btnStart) {
            btnStart.addEventListener('click', () => {
                this.requestFullscreen();
                const prompt = document.getElementById('startFullscreenPrompt');
                if (prompt) prompt.classList.add('hidden');
            });
        }

        // 8. Form Buka Kunci dengan Token Pengawas CMD
        const formUnlock = document.getElementById('formUnlockExam');
        if (formUnlock) {
            formUnlock.addEventListener('submit', async (e) => {
                e.preventDefault();
                const inputToken = document.getElementById('inputUnlockToken').value.trim();
                const errorEl = document.getElementById('unlockErrorMsg');
                const btnSubmit = document.getElementById('btnSubmitUnlock');

                btnSubmit.disabled = true;
                btnSubmit.textContent = 'Memverifikasi Token...';
                if (errorEl) errorEl.classList.add('hidden');

                try {
                    const res = await fetch('api/verify_unlock_token.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            token: this.token,
                            unlock_token: inputToken
                        })
                    });
                    const result = await res.json();

                    if (result.success) {
                        this.isLocked = false;
                        document.getElementById('examLockoutScreen').classList.add('hidden');
                        document.getElementById('inputUnlockToken').value = '';
                        this.requestFullscreen();
                    } else {
                        if (errorEl) {
                            errorEl.textContent = result.message || 'Token tidak sesuai!';
                            errorEl.classList.remove('hidden');
                        }
                    }
                } catch (err) {
                    console.error(err);
                    if (errorEl) {
                        errorEl.textContent = 'Terjadi kesalahan server saat verifikasi token.';
                        errorEl.classList.remove('hidden');
                    }
                } finally {
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Buka Kunci & Lanjutkan Ujian';
                }
            });
        }
    }

    requestFullscreen() {
        const elem = document.documentElement;
        if (elem.requestFullscreen) {
            elem.requestFullscreen().catch(() => {});
        } else if (elem.webkitRequestFullscreen) {
            elem.webkitRequestFullscreen();
        } else if (elem.msRequestFullscreen) {
            elem.msRequestFullscreen();
        }
    }

    async triggerLockout(alasan) {
        this.isLocked = true;
        const screen = document.getElementById('examLockoutScreen');
        if (screen) screen.classList.remove('hidden');

        try {
            await fetch('api/report_violation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    token: this.token,
                    alasan: alasan
                })
            });
        } catch (e) {
            console.error('Lockout report error:', e);
        }
    }

    setupEventListeners() {
        document.getElementById('btnPrev').addEventListener('click', () => {
            if (this.currentNomor > 1) {
                this.loadQuestion(this.currentNomor - 1);
            }
        });

        document.getElementById('btnNext').addEventListener('click', () => {
            if (this.currentNomor < this.totalSoal) {
                this.loadQuestion(this.currentNomor + 1);
            } else {
                this.showFinishModal();
            }
        });

        const chkRagu = document.getElementById('chkRagu');
        chkRagu.addEventListener('change', (e) => {
            this.handleRaguChange(e.target.checked);
        });

        const btnOpenPalette = document.getElementById('btnOpenPalette');
        const paletteModal = document.getElementById('paletteModal');
        const btnClosePalette = document.getElementById('btnClosePalette');

        const openPalette = () => {
            paletteModal.classList.remove('hidden');
        };

        const closePalette = () => {
            paletteModal.classList.add('hidden');
        };

        btnOpenPalette.addEventListener('click', openPalette);
        btnClosePalette.addEventListener('click', closePalette);

        paletteModal.addEventListener('click', (e) => {
            if (e.target === paletteModal) closePalette();
        });

        document.querySelectorAll('.btn-font-size').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const size = e.currentTarget.dataset.size;
                document.body.classList.remove('font-size-sm', 'font-size-md', 'font-size-lg');
                document.body.classList.add(`font-size-${size}`);
                document.querySelectorAll('.btn-font-size').forEach(b => {
                    b.classList.remove('bg-blue-700', 'text-white', 'font-bold');
                    b.classList.add('text-slate-300');
                });
                e.currentTarget.classList.add('bg-blue-700', 'text-white', 'font-bold');
                e.currentTarget.classList.remove('text-slate-300');
            });
        });

        document.getElementById('btnCancelFinish').addEventListener('click', () => {
            document.getElementById('finishModal').classList.add('hidden');
        });

        document.getElementById('btnConfirmFinish').addEventListener('click', () => {
            this.submitExam();
        });

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.isLocked) {
                const finishM = document.getElementById('finishModal');
                if (finishM && !finishM.classList.contains('hidden')) {
                    finishM.classList.add('hidden');
                }
                const palM = document.getElementById('paletteModal');
                if (palM && !palM.classList.contains('hidden')) {
                    palM.classList.add('hidden');
                }
            }
        });
    }

    startTimer(seconds) {
        if (this.timerInterval) clearInterval(this.timerInterval);
        this.timerSeconds = seconds;

        const updateTimerDisplay = () => {
            const h = Math.floor(this.timerSeconds / 3600);
            const m = Math.floor((this.timerSeconds % 3600) / 60);
            const s = this.timerSeconds % 60;
            const formatted = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            
            const timerEl = document.getElementById('timerDisplay');
            if (timerEl) {
                timerEl.textContent = formatted;
                if (this.timerSeconds <= 300) {
                    timerEl.classList.add('text-red-500', 'animate-pulse');
                }
            }

            if (this.timerSeconds <= 0) {
                clearInterval(this.timerInterval);
                alert('Waktu ujian telah berakhir! Lembar jawaban akan otomatis dikirim.');
                this.submitExam();
            } else {
                this.timerSeconds--;
            }
        };

        updateTimerDisplay();
        this.timerInterval = setInterval(updateTimerDisplay, 1000);
    }

    async loadQuestion(nomor) {
        this.showLoading(true);
        try {
            const response = await fetch(`api/get_question.php?token=${encodeURIComponent(this.token)}&nomor=${nomor}`);
            const data = await response.json();

            if (!data.success) {
                alert(data.message || 'Gagal memuat soal');
                return;
            }

            this.currentNomor = data.nomor_aktif;
            this.totalSoal = data.total_soal;
            this.currentSoalData = data.soal;
            this.paletteData = data.palette;

            if (!this.timerInterval && data.sesi && data.sesi.sisa_detik !== undefined) {
                this.startTimer(data.sesi.sisa_detik);
            }

            this.renderStimulus(data.soal.stimulus);
            this.renderQuestion(data.soal);
            this.renderPalette(data.palette);
            this.updateNavButtons();

            const chkRagu = document.getElementById('chkRagu');
            chkRagu.checked = !!data.soal.is_ragu;

        } catch (err) {
            console.error(err);
            alert('Terjadi kendala koneksi saat memuat soal.');
        } finally {
            this.showLoading(false);
        }
    }

    renderStimulus(stimulus) {
        const container = document.getElementById('stimulusContent');
        const titleEl = document.getElementById('stimulusTitle');
        const sourceEl = document.getElementById('stimulusSource');

        if (stimulus && stimulus.konten) {
            titleEl.textContent = stimulus.judul || 'Informasi Wacana';
            container.innerHTML = stimulus.konten;
            sourceEl.textContent = stimulus.sumber ? `Sumber: ${stimulus.sumber}` : '';
            sourceEl.classList.remove('hidden');
        } else {
            titleEl.textContent = 'Petunjuk Soal';
            container.innerHTML = `
                <div class="p-8 text-slate-500 text-center">
                    <p class="font-bold text-slate-800 text-base">Soal ini berdiri mandiri tanpa teks wacana bacaan.</p>
                    <p class="text-sm mt-2 text-slate-600">Silakan baca butir pertanyaan dan tentukan pilihan jawaban terbaik pada panel kanan.</p>
                </div>
            `;
            sourceEl.classList.add('hidden');
        }

        document.getElementById('paneStimulus').scrollTop = 0;
    }

    renderQuestion(soal) {
        document.getElementById('questionNumberBadge').textContent = `SOAL NO. ${soal.nomor_urut}`;
        document.getElementById('subtesBadge').textContent = soal.subtes;

        const tipeLabels = {
            'pg_tunggal': 'Pilihan Ganda',
            'pg_kompleks': 'Pilihan Ganda Kompleks',
            'matriks_bs': 'Pernyataan (Benar / Salah)',
            'menjodohkan': 'Menjodohkan',
            'isian_singkat': 'Isian Singkat'
        };
        document.getElementById('tipeSoalBadge').textContent = tipeLabels[soal.tipe_soal] || soal.tipe_soal;

        document.getElementById('questionText').innerHTML = soal.pertanyaan;

        const optionsContainer = document.getElementById('optionsContainer');
        optionsContainer.innerHTML = '';

        const currentAns = soal.jawaban_tersimpan;

        switch (soal.tipe_soal) {
            case 'pg_tunggal':
                this.renderPgTunggal(optionsContainer, soal.opsi_jawaban, currentAns);
                break;
            case 'pg_kompleks':
                this.renderPgKompleks(optionsContainer, soal.opsi_jawaban, currentAns);
                break;
            case 'matriks_bs':
                this.renderMatriksBS(optionsContainer, soal.opsi_jawaban, currentAns);
                break;
            case 'menjodohkan':
                this.renderMenjodohkan(optionsContainer, soal.opsi_jawaban, currentAns);
                break;
            case 'isian_singkat':
                this.renderIsianSingkat(optionsContainer, currentAns);
                break;
        }

        document.getElementById('paneSoal').scrollTop = 0;
    }

    renderPgTunggal(container, opsiList, currentAns) {
        const wrapper = document.createElement('div');
        wrapper.className = 'space-y-2.5';
        wrapper.setAttribute('role', 'radiogroup');

        (opsiList || []).forEach(opt => {
            const item = document.createElement('div');
            const isSelected = (currentAns === opt.key);
            item.className = `cbt-option-item ${isSelected ? 'selected' : ''}`;
            item.setAttribute('tabindex', '0');
            item.setAttribute('role', 'radio');
            item.setAttribute('aria-checked', isSelected ? 'true' : 'false');
            item.innerHTML = `
                <div class="option-badge">${opt.key}</div>
                <div class="cbt-text flex-1 pt-0.5 text-slate-900 font-medium">${opt.text}</div>
            `;

            const selectThis = () => {
                wrapper.querySelectorAll('.cbt-option-item').forEach(el => {
                    el.classList.remove('selected');
                    el.setAttribute('aria-checked', 'false');
                });
                item.classList.add('selected');
                item.setAttribute('aria-checked', 'true');
                this.saveAnswer(opt.key);
            };

            item.addEventListener('click', selectThis);
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectThis();
                }
            });

            wrapper.appendChild(item);
        });

        container.appendChild(wrapper);
    }

    renderPgKompleks(container, opsiList, currentAns) {
        const wrapper = document.createElement('div');
        wrapper.className = 'space-y-2.5';
        wrapper.setAttribute('role', 'group');
        const selectedKeys = Array.isArray(currentAns) ? [...currentAns] : [];

        (opsiList || []).forEach(opt => {
            const item = document.createElement('div');
            const isSelected = selectedKeys.includes(opt.key);
            item.className = `cbt-option-item ${isSelected ? 'selected' : ''}`;
            item.setAttribute('tabindex', '0');
            item.setAttribute('role', 'checkbox');
            item.setAttribute('aria-checked', isSelected ? 'true' : 'false');
            item.innerHTML = `
                <div class="option-checkbox-badge">
                    ${isSelected ? '✓' : ''}
                </div>
                <div class="cbt-text flex-1 pt-0.5 text-slate-900 font-medium">${opt.text}</div>
            `;

            const toggleThis = () => {
                const idx = selectedKeys.indexOf(opt.key);
                if (idx > -1) {
                    selectedKeys.splice(idx, 1);
                    item.classList.remove('selected');
                    item.setAttribute('aria-checked', 'false');
                    item.querySelector('.option-checkbox-badge').textContent = '';
                } else {
                    selectedKeys.push(opt.key);
                    item.classList.add('selected');
                    item.setAttribute('aria-checked', 'true');
                    item.querySelector('.option-checkbox-badge').textContent = '✓';
                }
                this.saveAnswer([...selectedKeys]);
            };

            item.addEventListener('click', toggleThis);
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleThis();
                }
            });

            wrapper.appendChild(item);
        });

        container.appendChild(wrapper);
    }

    renderMatriksBS(container, opsiData, currentAns) {
        if (!opsiData || !opsiData.pernyataan) return;
        const kolom = opsiData.kolom || ['Benar', 'Salah'];
        const userState = (currentAns && typeof currentAns === 'object') ? { ...currentAns } : {};

        const table = document.createElement('table');
        table.className = 'w-full cbt-matrix-table text-sm border-collapse bg-white shadow-xs';
        
        let theadHtml = `<thead><tr class="bg-slate-100 text-slate-900 border-b border-slate-300">
            <th class="text-left font-bold p-3">Pernyataan</th>`;
        kolom.forEach(col => {
            theadHtml += `<th class="text-center font-bold w-24 p-3">${col}</th>`;
        });
        theadHtml += `</tr></thead>`;
        table.innerHTML = theadHtml;

        const tbody = document.createElement('tbody');
        opsiData.pernyataan.forEach(p => {
            const tr = document.createElement('tr');
            let tdHtml = `<td class="cbt-text text-slate-900 leading-relaxed font-medium p-3">${p.teks}</td>`;
            kolom.forEach(col => {
                const checked = (userState[p.id] === col) ? 'checked' : '';
                tdHtml += `
                    <td class="text-center align-middle p-3">
                        <label class="cursor-pointer inline-flex items-center justify-center">
                            <input type="radio" name="matrix_${p.id}" value="${col}" ${checked} class="w-5 h-5 text-blue-900 focus:ring-blue-900">
                        </label>
                    </td>
                `;
            });
            tr.innerHTML = tdHtml;

            tr.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    userState[p.id] = e.target.value;
                    this.saveAnswer({ ...userState });
                });
            });

            tbody.appendChild(tr);
        });

        table.appendChild(tbody);
        container.appendChild(table);
    }

    renderMenjodohkan(container, opsiData, currentAns) {
        if (!opsiData || !opsiData.premis || !opsiData.target) return;
        const userState = (currentAns && typeof currentAns === 'object') ? { ...currentAns } : {};

        const wrapper = document.createElement('div');
        wrapper.className = 'space-y-3';

        opsiData.premis.forEach(p => {
            const card = document.createElement('div');
            card.className = 'p-3.5 border rounded border-slate-300 bg-white';
            
            let optionsHtml = `<option value="">-- Pilih Pasangan yang Sesuai --</option>`;
            opsiData.target.forEach(t => {
                const isSelected = (userState[p.id] === t.id) ? 'selected' : '';
                optionsHtml += `<option value="${t.id}" ${isSelected}>${t.teks}</option>`;
            });

            card.innerHTML = `
                <div class="font-bold text-slate-900 mb-2 cbt-text">
                    &bull; ${p.teks}
                </div>
                <div>
                    <select class="match-select w-full p-2.5 bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded focus:border-blue-900">
                        ${optionsHtml}
                    </select>
                </div>
            `;

            card.querySelector('.match-select').addEventListener('change', (e) => {
                userState[p.id] = e.target.value;
                this.saveAnswer({ ...userState });
            });

            wrapper.appendChild(card);
        });

        container.appendChild(wrapper);
    }

    renderIsianSingkat(container, currentAns) {
        const wrapper = document.createElement('div');
        wrapper.className = 'p-5 bg-slate-50 border rounded border-slate-300 max-w-md';
        wrapper.innerHTML = `
            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Jawaban Singkat:</label>
            <input type="text" id="inputIsianSingkat" value="${currentAns || ''}" placeholder="Ketik jawaban..." class="w-full px-3.5 py-2 text-base border border-slate-400 rounded focus:border-blue-900 bg-white font-bold text-slate-900">
            <p class="text-xs text-slate-500 mt-2">Dapat menggunakan koma (,) atau titik (.) untuk desimal.</p>
        `;

        const input = wrapper.querySelector('#inputIsianSingkat');
        let debounceTimer;
        input.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                this.saveAnswer(e.target.value.trim());
            }, 300);
        });

        container.appendChild(wrapper);
    }

    renderPalette(palette) {
        const grid = document.getElementById('paletteGrid');
        if (!grid) return;
        grid.innerHTML = '';

        palette.forEach(item => {
            const cell = document.createElement('button');
            const isCurrent = (item.nomor === this.currentNomor);

            let statusClass = 'unanswered';
            if (item.is_ragu) {
                statusClass = 'ragu';
            } else if (item.is_answered) {
                statusClass = 'answered';
            }

            cell.className = `anbk-palette-cell ${statusClass} ${isCurrent ? 'current' : ''}`;
            
            let displayAns = '-';
            if (item.is_answered && item.preview_jawaban) {
                displayAns = item.preview_jawaban;
            }

            cell.innerHTML = `
                <span class="cell-no">${item.nomor}</span>
                <span class="cell-ans">${displayAns}</span>
            `;

            cell.addEventListener('click', () => {
                const modal = document.getElementById('paletteModal');
                if (modal) modal.classList.add('hidden');
                this.loadQuestion(item.nomor);
            });

            grid.appendChild(cell);
        });
    }

    updateNavButtons() {
        const btnPrev = document.getElementById('btnPrev');
        const btnNext = document.getElementById('btnNext');

        btnPrev.disabled = (this.currentNomor <= 1);

        if (this.currentNomor >= this.totalSoal) {
            btnNext.innerHTML = `<span>SELESAI UJIAN</span> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>`;
            btnNext.className = 'bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white text-xs md:text-sm font-black px-6 py-2.5 rounded flex items-center gap-2 transition';
        } else {
            btnNext.innerHTML = `<span>SOAL BERIKUTNYA</span> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>`;
            btnNext.className = 'bg-[#1e40af] hover:bg-[#1d4ed8] active:bg-[#1e3a8a] text-white text-xs md:text-sm font-bold px-6 py-2.5 rounded flex items-center gap-2 transition';
        }
    }

    async saveAnswer(jawaban) {
        const chkRagu = document.getElementById('chkRagu');
        const isRagu = chkRagu.checked;

        try {
            const response = await fetch('api/save_answer.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    token: this.token,
                    nomor: this.currentNomor,
                    jawaban: jawaban,
                    is_ragu: isRagu
                })
            });

            const result = await response.json();
            if (result.success) {
                this.refreshPaletteStatus(this.currentNomor, jawaban, isRagu);
            }
        } catch (err) {
            console.error('Auto-save error:', err);
        }
    }

    handleRaguChange(isRagu) {
        if (this.currentSoalData) {
            const currentAns = this.currentSoalData.jawaban_tersimpan;
            this.saveAnswer(currentAns);
        }
    }

    refreshPaletteStatus(nomor, jawaban, isRagu) {
        const cells = document.querySelectorAll('.anbk-palette-cell');
        if (cells[nomor - 1]) {
            const cell = cells[nomor - 1];
            cell.classList.remove('ragu', 'answered', 'unanswered');

            const hasAnswer = (jawaban !== null && jawaban !== '' && (!Array.isArray(jawaban) || jawaban.length > 0));

            if (isRagu) {
                cell.classList.add('ragu');
            } else if (hasAnswer) {
                cell.classList.add('answered');
            } else {
                cell.classList.add('unanswered');
            }

            const ansSpan = cell.querySelector('.cell-ans');
            if (ansSpan) {
                if (typeof jawaban === 'string') {
                    ansSpan.textContent = jawaban.toUpperCase();
                } else if (Array.isArray(jawaban)) {
                    ansSpan.textContent = (jawaban.length > 0) ? (jawaban.length === 1 ? jawaban[0] : '✓') : '-';
                } else if (hasAnswer) {
                    ansSpan.textContent = '✓';
                } else {
                    ansSpan.textContent = '-';
                }
            }
        }
    }

    showFinishModal() {
        let answered = 0;
        let ragu = 0;
        let empty = 0;

        document.querySelectorAll('.anbk-palette-cell').forEach(cell => {
            if (cell.classList.contains('ragu')) {
                ragu++;
            } else if (cell.classList.contains('answered')) {
                answered++;
            } else {
                empty++;
            }
        });

        document.getElementById('statAnswered').textContent = answered;
        document.getElementById('statRagu').textContent = ragu;
        document.getElementById('statEmpty').textContent = empty;

        document.getElementById('finishModal').classList.remove('hidden');
    }

    async submitExam() {
        this.isSubmitting = true;
        const confirmBtn = document.getElementById('btnConfirmFinish');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Menyimpan...';
        }

        try {
            const response = await fetch('api/submit_exam.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: this.token })
            });

            const result = await response.json();
            if (result.success && result.redirect) {
                window.location.href = result.redirect;
            } else {
                alert(result.message || 'Gagal mengirim ujian');
                this.isSubmitting = false;
                if (confirmBtn) confirmBtn.disabled = false;
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan saat mengumpulkan ujian.');
            this.isSubmitting = false;
            if (confirmBtn) confirmBtn.disabled = false;
        }
    }

    showLoading(isLoading) {
        const loader = document.getElementById('cbtLoader');
        if (loader) {
            loader.classList.toggle('hidden', !isLoading);
        }
    }
}
