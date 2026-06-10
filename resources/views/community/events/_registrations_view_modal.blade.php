{{-- REGISTRATIONS VIEW MODAL --}}
<div id="eventRegsModal" class="me-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="regsModalTitle" hidden>
    <div class="me-modal me-modal--xl" role="document">

        {{-- Header --}}
        <div class="me-modal__header">
            <div class="me-modal__header-left">
                <p class="me-modal__sublabel">Registrations for</p>
                <h2 id="regsModalTitle" class="me-modal__title">Event Name</h2>
                <p id="regsModalMeta" class="me-modal__meta"></p>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <button type="button" id="regsExportBtn" class="me-btn me-btn--outline" style="display:none;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export CSV
                </button>
                <button type="button" class="me-modal__close" id="closeRegsModal" aria-label="Close">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="me-modal__body" style="padding-top:0;">

            {{-- Loading --}}
            <div id="regsLoading" class="me-regs-loading">
                <div class="me-regs-spinner"></div>
                <p>Loading registrations…</p>
            </div>

            {{-- Error --}}
            <div id="regsError" class="me-alert me-alert--danger" style="margin-top:20px;" hidden></div>

            {{-- Empty --}}
            <div id="regsEmpty" class="me-regs-empty" hidden>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <h3>No registrations yet</h3>
                <p>No one has registered for this event yet. Check back later.</p>
            </div>

            {{-- Stats Bar --}}
            <div id="regsStats" class="me-regs-stats" hidden>
                <div class="me-regs-stat">
                    <span class="me-regs-stat__number" id="regsStatRegistrants">0</span>
                    <span class="me-regs-stat__label">Registrants</span>
                </div>
                <div class="me-regs-stat">
                    <span class="me-regs-stat__number" id="regsStatPeople">0</span>
                    <span class="me-regs-stat__label">Total People</span>
                </div>
            </div>

            {{-- Table --}}
            <div id="regsTableWrap" class="me-regs-table-wrap" hidden>
                <table class="me-regs-table" role="grid">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Country</th>
                            <th scope="col">Batch</th>
                            <th scope="col">People</th>
                            <th scope="col">Message</th>
                            <th scope="col">Registered At</th>
                        </tr>
                    </thead>
                    <tbody id="regsTableBody"></tbody>
                </table>
            </div>

        </div>

        {{-- Footer --}}
        <div class="me-modal__footer">
            <button type="button" class="me-btn me-btn--outline" id="closeRegsModalFooter">Close</button>
        </div>

    </div>
</div>