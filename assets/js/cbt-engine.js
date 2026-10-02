// assets/js/cbt-engine.js - Engine Interaktif CBT Pusmendik

class CBTEngine {
    constructor(config) {
        this.token = config.token;
        this.currentNomor = 1;
        this.totalSoal = 10;
        this.timerSeconds = 0;
        this.timerInterval = null;
        this.paletteData = [];
        this.currentSoalData = null;
        this.answers = {};
        this.isRaguMap = {};
        this.fontSizeState = 'font-size-md';

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadQuestion(this.currentNomor);
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

        // Drawer Daftar Soal Toggle
        const btnTogglePalette = document.getElementById('btnTogglePalette');
        const paletteDrawer = document.getElementById('paletteDrawer');
        const btnClosePalette = document.getElementById('btnClosePalette');
        const paletteOverlay = document.getElementById('paletteOverlay');

        const toggleDrawer = () => {
            paletteDrawer.classList.toggle('open');
            paletteOverlay.classList.toggle('hidden');
        };

        btnTogglePalette.addEventListener('click', toggleDrawer);
        btnClosePalette.addEventListener('click', toggleDrawer);
        paletteOverlay.addEventListener('click', toggleDrawer);

        // Pengatur Ukuran Font (A- / A / A+)
        document.querySelectorAll('.btn-font-size').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const size = e.currentTarget.dataset.size;
                document.body.classList.remove('font-size-sm', 'font-size-md', 'font-size-lg');
                document.body.classList.add(`font-size-${size}`);
                document.querySelectorAll('.btn-font-size').forEach(b => b.classList.remove('bg-blue-100', 'text-blue-700', 'font-bold'));
                e.currentTarget.classList.add('bg-blue-100', 'text-blue-700', 'font-bold');
            });
        });

        // Modal Selesai
        document.getElementById('btnCancelFinish').addEventListener('click', () => {
            document.getElementById('finishModal').classList.add('hidden');
        });

        document.getElementById('btnConfirmFinish').addEventListener('click', () => {
            this.submitExam();
        });

        // Dukungan Keyboard Escape untuk menutup Drawer & Modal (R-32)
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.getElementById('finishModal');
                if (modal && !modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
                const drawer = document.getElementById('paletteDrawer');
                if (drawer && drawer.classList.contains('open')) {
                    drawer.classList.remove('open');
                    document.getElementById('paletteOverlay').classList.add('hidden');
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
                if (this.timerSeconds <= 300) { // Sisa 5 menit warna merah
                    timerEl.classList.add('text-red-600', 'animate-pulse');
                }
            }

            if (this.timerSeconds <= 0) {
                clearInterval(this.timerInterval);
                alert('Waktu ujian telah habis! Jawaban Anda akan otomatis dikirim.');
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

            // Timer init jika belum berjalan
            if (!this.timerInterval && data.sesi && data.sesi.sisa_detik !== undefined) {
                this.startTimer(data.sesi.sisa_detik);
            }

            this.renderStimulus(data.soal.stimulus);
            this.renderQuestion(data.soal);
            this.renderPalette(data.palette);
            this.updateNavButtons();

            // Set state ragu-ragu
            const chkRagu = document.getElementById('chkRagu');
            chkRagu.checked = !!data.soal.is_ragu;

        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan koneksi saat memuat soal.');
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
            titleEl.textContent = 'Petunjuk Pengerjaan';
            container.innerHTML = `
                <div class="p-6 text-slate-500 text-center">
                    <p class="font-medium text-slate-700">Soal ini berdiri mandiri tanpa wacana panjang.</p>
                    <p class="text-sm mt-1">Silakan langsung baca pertanyaan dan pilih jawaban terbaik di sisi kanan.</p>
                </div>
            `;
            sourceEl.classList.add('hidden');
        }

        // Reset scroll ke atas tiap kali pindah soal
        document.getElementById('paneStimulus').scrollTop = 0;
    }

    renderQuestion(soal) {
        document.getElementById('questionNumberBadge').textContent = `Soal No. ${soal.nomor_urut}`;
        document.getElementById('subtesBadge').textContent = soal.subtes;

        // Badge Tipe Soal
        const tipeLabels = {
            'pg_tunggal': 'Pilihan Ganda',
            'pg_kompleks': 'Pilihan Ganda Kompleks (Bisa > 1 Jawaban)',
            'matriks_bs': 'Pernyataan (Benar / Salah)',
            'menjodohkan': 'Menjodohkan',
            'isian_singkat': 'Isian Singkat'
        };
        document.getElementById('tipeSoalBadge').textContent = tipeLabels[soal.tipe_soal] || soal.tipe_soal;

        // Teks Pertanyaan
        document.getElementById('questionText').innerHTML = soal.pertanyaan;

        // Container Opsi
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
        wrapper.setAttribute('aria-label', 'Pilihan Ganda');

        (opsiList || []).forEach(opt => {
            const item = document.createElement('div');
            const isSelected = (currentAns === opt.key);
            item.className = `cbt-option-item ${isSelected ? 'selected' : ''}`;
            item.setAttribute('tabindex', '0');
            item.setAttribute('role', 'radio');
            item.setAttribute('aria-checked', isSelected ? 'true' : 'false');
            item.innerHTML = `
                <div class="option-badge">${opt.key}</div>
                <div class="cbt-text flex-1 pt-0.5 text-slate-800">${opt.text}</div>
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
        wrapper.setAttribute('aria-label', 'Pilihan Ganda Kompleks');
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
                <div class="cbt-text flex-1 pt-0.5 text-slate-800">${opt.text}</div>
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
        table.className = 'w-full cbt-matrix-table text-sm border-collapse bg-white rounded-lg shadow-sm';
        
        let theadHtml = `<thead><tr class="bg-slate-100 text-slate-700">
            <th class="text-left font-semibold">Pernyataan</th>`;
        kolom.forEach(col => {
            theadHtml += `<th class="text-center font-semibold w-24">${col}</th>`;
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
                        <label class="cursor-pointer inline-flex items-center justify-center p-2">
                            <input type="radio" name="matrix_${p.id}" value="${col}" ${checked} class="w-4 h-4 text-blue-600 focus:ring-blue-500">
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
            card.className = 'p-4 border rounded-lg bg-white shadow-xs border-slate-200';
            
            let optionsHtml = `<option value="">-- Pilih Pasangan yang Tepat --</option>`;
            opsiData.target.forEach(t => {
                const isSelected = (userState[p.id] === t.id) ? 'selected' : '';
                optionsHtml += `<option value="${t.id}" ${isSelected}>${t.teks}</option>`;
            });

            card.innerHTML = `
                <div class="font-medium text-slate-800 mb-2 cbt-text flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600 inline-block"></span>
                    <span>${p.teks}</span>
                </div>
                <div>
                    <select class="match-select w-full p-2.5 bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
        wrapper.className = 'p-6 bg-slate-50 border rounded-lg border-slate-200 max-w-lg';
        wrapper.innerHTML = `
            <label class="block text-sm font-semibold text-slate-700 mb-2">Jawaban Anda:</label>
            <input type="text" id="inputIsianSingkat" value="${currentAns || ''}" placeholder="Ketik jawaban di sini..." class="w-full px-4 py-2.5 text-base border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
            <p class="text-xs text-slate-500 mt-2">Untuk angka desimal, Anda dapat menggunakan koma (,) atau titik (.).</p>
        `;

        const input = wrapper.querySelector('#inputIsianSingkat');
        let debounceTimer;
        input.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                this.saveAnswer(e.target.value.trim());
            }, 400);
        });

        container.appendChild(wrapper);
    }

    renderPalette(palette) {
        const grid = document.getElementById('paletteGrid');
        grid.innerHTML = '';

        palette.forEach(item => {
            const btn = document.createElement('button');
            btn.className = `palette-btn ${item.nomor === this.currentNomor ? 'active' : ''}`;
            
            if (item.is_ragu) {
                btn.classList.add('ragu');
            } else if (item.is_answered) {
                btn.classList.add('answered');
            }

            btn.innerHTML = `
                <span>${item.nomor}</span>
                <span class="palette-badge">${item.preview_jawaban || ''}</span>
            `;

            btn.addEventListener('click', () => {
                document.getElementById('paletteDrawer').classList.remove('open');
                document.getElementById('paletteOverlay').classList.add('hidden');
                this.loadQuestion(item.nomor);
            });

            grid.appendChild(btn);
        });
    }

    updateNavButtons() {
        const btnPrev = document.getElementById('btnPrev');
        const btnNext = document.getElementById('btnNext');

        btnPrev.disabled = (this.currentNomor <= 1);
        btnPrev.classList.toggle('opacity-50', this.currentNomor <= 1);
        btnPrev.classList.toggle('cursor-not-allowed', this.currentNomor <= 1);

        if (this.currentNomor >= this.totalSoal) {
            btnNext.textContent = 'Selesai Ujian ✓';
            btnNext.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btnNext.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
        } else {
            btnNext.textContent = 'Soal Berikutnya >';
            btnNext.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
            btnNext.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }
    }

    async saveAnswer(jawaban) {
        const chkRagu = document.getElementById('chkRagu');
        const isRagu = chkRagu.checked;
        const toast = document.getElementById('saveIndicator');

        if (toast) {
            toast.textContent = 'Menyimpan...';
            toast.className = 'text-xs text-amber-600 font-medium';
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
                    toast.textContent = `Tersimpan ✓ (${result.saved_at})`;
                    toast.className = 'text-xs text-emerald-600 font-medium';
                }
                // Refresh status palette
                this.refreshPaletteStatus(this.currentNomor, jawaban, isRagu);
            }
        } catch (err) {
            console.error('Auto-save error:', err);
            if (toast) {
                toast.textContent = 'Gagal menyimpan!';
                toast.className = 'text-xs text-red-600 font-medium';
            }
        }
    }

    handleRaguChange(isRagu) {
        // Ambil jawaban saat ini dan kirim ulang dengan status ragu baru
        if (this.currentSoalData) {
            const currentAns = this.currentSoalData.jawaban_tersimpan;
            this.saveAnswer(currentAns);
        }
    }

    refreshPaletteStatus(nomor, jawaban, isRagu) {
        const buttons = document.querySelectorAll('.palette-btn');
        if (buttons[nomor - 1]) {
            const btn = buttons[nomor - 1];
            btn.classList.remove('ragu', 'answered');

            const hasAnswer = (jawaban !== null && jawaban !== '' && (!Array.isArray(jawaban) || jawaban.length > 0));

            if (isRagu) {
                btn.classList.add('ragu');
            } else if (hasAnswer) {
                btn.classList.add('answered');
            }

            const badge = btn.querySelector('.palette-badge');
            if (badge) {
                if (typeof jawaban === 'string') {
                    badge.textContent = jawaban.toUpperCase();
                } else if (Array.isArray(jawaban)) {
                    badge.textContent = jawaban.join(',');
                } else if (hasAnswer) {
                    badge.textContent = '✓';
                } else {
                    badge.textContent = '';
                }
            }
        }
    }

    showFinishModal() {
        // Hitung statistik jawaban dari palette
        let answered = 0;
        let ragu = 0;
        let empty = 0;

        document.querySelectorAll('.palette-btn').forEach(btn => {
            if (btn.classList.contains('ragu')) {
                ragu++;
            } else if (btn.classList.contains('answered')) {
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
        const confirmBtn = document.getElementById('btnConfirmFinish');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Memproses Nilai...';
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
                if (confirmBtn) confirmBtn.disabled = false;
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan saat finalisasi nilai.');
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
