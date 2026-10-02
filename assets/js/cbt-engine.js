// assets/js/cbt-engine.js - Engine CBT Pusmendik ANBK dengan Kiosk Lockdown

class CBTEngine {
    constructor(config) {
        this.token = config.token;
        this.currentNomor = 1;
        this.totalSoal = 10;
        this.timerSeconds = 0;
        this.timerInterval = null;
        this.paletteData = [];
        this.currentSoalData = null;
        this.violationCount = 0;
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

        // 2. Peringatan Reload / Menutup Halaman
        window.addEventListener('beforeunload', (e) => {
            if (!this.isSubmitting) {
                e.preventDefault();
                e.returnValue = 'Ujian sedang berlangsung! Jangan tinggalkan halaman ini.';
            }
        });

        // 3. Blokir Klik Kanan
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            return false;
        });

        // 4. Blokir Tombol Inspect / Shortcuts
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

        // 5. Deteksi Berpindah Tab atau Keluar Jendela Browser
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && !this.isSubmitting) {
                this.recordViolation('Berpindah tab atau membuka aplikasi lain');
            }
        });

        // 6. Deteksi Keluar Layar Penuh
        const onFullscreenChange = () => {
            const isFull = !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement);
            const overlay = document.getElementById('fullscreenLockOverlay');
            const prompt = document.getElementById('startFullscreenPrompt');

            // Jangan munculkan warning jika modal awal masih terbuka
            if (!isFull && !this.isSubmitting && (!prompt || prompt.classList.contains('hidden'))) {
                this.recordViolation('Keluar dari mode layar penuh (Fullscreen)');
            } else if (isFull && overlay) {
                overlay.classList.add('hidden');
            }
        };

        document.addEventListener('fullscreenchange', onFullscreenChange);
        document.addEventListener('webkitfullscreenchange', onFullscreenChange);

        // 7. Handler Tombol Awal Masuk Fullscreen
        const btnStart = document.getElementById('btnStartExamFullscreen');
        if (btnStart) {
            btnStart.addEventListener('click', () => {
                this.requestFullscreen();
                const prompt = document.getElementById('startFullscreenPrompt');
                if (prompt) prompt.classList.add('hidden');
            });
        }

        // 8. Handler Tombol Re-enter Fullscreen dari Overlay
        const btnReenter = document.getElementById('btnReenterFullscreen');
        if (btnReenter) {
            btnReenter.addEventListener('click', () => {
                this.requestFullscreen();
                const overlay = document.getElementById('fullscreenLockOverlay');
                if (overlay) overlay.classList.add('hidden');
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

    recordViolation(reason) {
        this.violationCount++;
        const elCount = document.getElementById('violationCount');
        if (elCount) elCount.textContent = this.violationCount;

        const overlay = document.getElementById('fullscreenLockOverlay');
        if (overlay) overlay.classList.remove('hidden');
    }

    setupEventListeners() {
        // Navigasi Bawah
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

        // Checkbox Ragu-ragu
        const chkRagu = document.getElementById('chkRagu');
        chkRagu.addEventListener('change', (e) => {
            this.handleRaguChange(e.target.checked);
        });

        // Modal Daftar Soal Khas ANBK
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

        // Tombol Fullscreen Header
        const btnToggleFs = document.getElementById('btnToggleFullscreen');
        if (btnToggleFs) {
            btnToggleFs.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    this.requestFullscreen();
                } else {
                    document.exitFullscreen().catch(() => {});
                }
            });
        }

        // Pengatur Ukuran Font
        document.querySelectorAll('.btn-font-size').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const size = e.currentTarget.dataset.size;
                document.body.classList.remove('font-size-sm', 'font-size-md', 'font-size-lg');
                document.body.classList.add(`font-size-${size}`);
                document.querySelectorAll('.btn-font-size').forEach(b => {
                    b.classList.remove('bg-blue-500', 'text-white', 'font-bold');
                    b.classList.add('text-blue-200');
                });
                e.currentTarget.classList.add('bg-blue-500', 'text-white', 'font-bold');
                e.currentTarget.classList.remove('text-blue-200');
            });
        });

        // Modal Selesai Ujian
        document.getElementById('btnCancelFinish').addEventListener('click', () => {
            document.getElementById('finishModal').classList.add('hidden');
        });

        document.getElementById('btnConfirmFinish').addEventListener('click', () => {
            this.submitExam();
        });

        // Tombol Escape Menutup Dialog
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
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
                alert('Waktu ujian telah berakhir! Lembar jawaban akan otomatis dikirimkan.');
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
            titleEl.textContent = 'Petunjuk Butir Soal';
            container.innerHTML = `
                <div class="p-8 text-slate-500 text-center">
                    <p class="font-bold text-slate-700 text-base">Soal ini berdiri sendiri tanpa teks wacana panjang.</p>
                    <p class="text-sm mt-2">Silakan cermati pertanyaan dan tentukan pilihan jawaban terbaik pada panel sebelah kanan.</p>
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
        wrapper.className = 'space-y-3';
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
                <div class="cbt-text flex-1 pt-0.5 text-slate-800 font-medium">${opt.text}</div>
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
        wrapper.className = 'space-y-3';
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
                <div class="cbt-text flex-1 pt-0.5 text-slate-800 font-medium">${opt.text}</div>
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
        table.className = 'w-full cbt-matrix-table text-sm border-collapse bg-white rounded-lg shadow-xs';
        
        let theadHtml = `<thead><tr class="bg-slate-100 text-slate-800">
            <th class="text-left font-bold">Pernyataan</th>`;
        kolom.forEach(col => {
            theadHtml += `<th class="text-center font-bold w-28">${col}</th>`;
        });
        theadHtml += `</tr></thead>`;
        table.innerHTML = theadHtml;

        const tbody = document.createElement('tbody');
        opsiData.pernyataan.forEach(p => {
            const tr = document.createElement('tr');
            let tdHtml = `<td class="cbt-text text-slate-800 leading-relaxed font-medium">${p.teks}</td>`;
            kolom.forEach(col => {
                const checked = (userState[p.id] === col) ? 'checked' : '';
                tdHtml += `
                    <td class="text-center align-middle">
                        <label class="cursor-pointer inline-flex items-center justify-center p-2.5">
                            <input type="radio" name="matrix_${p.id}" value="${col}" ${checked} class="w-5 h-5 text-blue-600 focus:ring-blue-500">
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
        wrapper.className = 'space-y-4';

        opsiData.premis.forEach(p => {
            const card = document.createElement('div');
            card.className = 'p-4 border rounded-lg bg-white shadow-xs border-slate-300';
            
            let optionsHtml = `<option value="">-- Pilih Pasangan yang Tepat --</option>`;
            opsiData.target.forEach(t => {
                const isSelected = (userState[p.id] === t.id) ? 'selected' : '';
                optionsHtml += `<option value="${t.id}" ${isSelected}>${t.teks}</option>`;
            });

            card.innerHTML = `
                <div class="font-bold text-slate-800 mb-2 cbt-text flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-700 inline-block"></span>
                    <span>${p.teks}</span>
                </div>
                <div>
                    <select class="match-select w-full p-2.5 bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-md focus:ring-2 focus:ring-blue-600">
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
        wrapper.className = 'p-6 bg-slate-50 border rounded-lg border-slate-300 max-w-md';
        wrapper.innerHTML = `
            <label class="block text-sm font-bold text-slate-700 mb-2">Ketikkan Jawaban Anda:</label>
            <input type="text" id="inputIsianSingkat" value="${currentAns || ''}" placeholder="Tulis jawaban..." class="w-full px-4 py-2.5 text-base border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-600 bg-white font-semibold">
            <p class="text-xs text-slate-500 mt-2">Untuk bilangan desimal dapat memakai tanda koma (,) atau titik (.).</p>
        `;

        const input = wrapper.querySelector('#inputIsianSingkat');
        let debounceTimer;
        input.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                this.saveAnswer(e.target.value.trim());
            }, 350);
        });

        container.appendChild(wrapper);
    }

    // RENDER PALETTE MODAL KHAS ANBK PUSMENDIK
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
            btnNext.className = 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs md:text-sm font-black px-6 py-2.5 rounded-lg flex items-center gap-2 transition shadow-xs';
        } else {
            btnNext.innerHTML = `<span>SOAL BERIKUTNYA</span> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>`;
            btnNext.className = 'bg-[#1e40af] hover:bg-[#1d4ed8] active:bg-[#1e3a8a] text-white text-xs md:text-sm font-bold px-6 py-2.5 rounded-lg flex items-center gap-2 transition shadow-xs';
        }
    }

    async saveAnswer(jawaban) {
        const chkRagu = document.getElementById('chkRagu');
        const isRagu = chkRagu.checked;
        const toast = document.getElementById('saveIndicator');

        if (toast) {
            toast.textContent = 'Menyimpan...';
            toast.className = 'text-xs text-amber-400 font-semibold px-2 py-1 bg-blue-950/60 rounded border border-blue-900';
        }

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
                if (toast) {
                    toast.textContent = 'Tersimpan ✓';
                    toast.className = 'text-xs text-emerald-400 font-semibold px-2 py-1 bg-blue-950/60 rounded border border-blue-900';
                }
                this.refreshPaletteStatus(this.currentNomor, jawaban, isRagu);
            }
        } catch (err) {
            console.error('Auto-save error:', err);
            if (toast) {
                toast.textContent = 'Koneksi lambat';
                toast.className = 'text-xs text-red-400 font-semibold px-2 py-1 bg-blue-950/60 rounded border border-blue-900';
            }
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
            confirmBtn.textContent = 'Menyimpan & Menghitung Nilai...';
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
            alert('Terjadi kesalahan saat finalisasi ujian.');
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
