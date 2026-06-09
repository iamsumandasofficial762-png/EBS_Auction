<style>
    .d-none { display: none !important; }
    .auction-dashboard { color: #10233f; }
    .auction-dashboard__intro { align-items: center; background: linear-gradient(135deg, #eff6ff, #ffffff); border: 1px solid #cfe0f8; border-radius: 8px; display: flex; justify-content: space-between; margin-bottom: 20px; padding: 22px; }
    .auction-dashboard__intro h2 { font-size: 28px; font-weight: 700; margin: 4px 0 8px; }
    .auction-dashboard__intro p { color: #66706a; margin: 0; max-width: 720px; }
    .auction-kicker { color: #1769c2; font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .auction-tabs { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 22px; }
    .auction-tab { align-items: center; background: #fff; border: 1px solid #dfe7dc; border-radius: 8px; color: #1e2d22; display: inline-flex; gap: 8px; padding: 10px 14px; text-decoration: none; }
    .auction-tab:hover, .auction-tab.is-active { background: #5f9bea; border-color: #5f9bea; color: #fff; text-decoration: none; }
    .auction-tab strong { align-items: center; background: rgba(95, 155, 234, .14); border-radius: 999px; display: inline-flex; font-size: 12px; height: 24px; justify-content: center; min-width: 24px; padding: 0 7px; }
    .auction-tab.is-active strong, .auction-tab:hover strong { background: rgba(255, 255, 255, .18); }
    .auction-grid { display: grid; gap: 18px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .auction-card { background: #fff; border: 1px solid #d6e4f8; border-radius: 8px; box-shadow: 0 10px 24px rgba(48, 111, 196, .08); overflow: hidden; }
    .auction-card__media { align-items: center; background: #fff; border-bottom: 1px solid #edf3fb; display: flex; height: 138px; justify-content: center; padding: 12px; position: relative; }
    .auction-card__media img { height: 100%; object-fit: contain; padding: 0; width: 100%; }
    .auction-image-slider { height: 100%; overflow: hidden; position: relative; width: 100%; }
    .auction-slider-track { display: flex; height: 100%; transition: transform .25s ease; width: 100%; }
    .auction-slider-slide { flex: 0 0 100%; height: 100%; min-width: 100%; }
    .auction-slider-slide img { display: block; height: 100%; object-fit: contain; width: 100%; }
    .auction-slider-btn { align-items: center; background: rgba(0, 0, 0, .45); border: 0; border-radius: 50%; color: #fff; cursor: pointer; display: inline-flex; height: 28px; justify-content: center; line-height: 1; padding: 0; position: absolute; top: 50%; transform: translateY(-50%); width: 28px; z-index: 3; }
    .auction-slider-btn--prev { left: 8px; }
    .auction-slider-btn--next { right: 8px; }
    .auction-slider-dots { bottom: 8px; display: flex; gap: 5px; justify-content: center; left: 0; position: absolute; right: 0; z-index: 3; }
    .auction-slider-dots button { background: rgba(23, 105, 194, .35); border: 0; border-radius: 50%; height: 7px; padding: 0; width: 7px; }
    .auction-slider-dots button.is-active { background: #1769c2; }
    .auction-image-slider--detail { height: 420px; }
    .auction-card__badge { align-items: center; background: #1769c2; border-radius: 999px; color: #fff; display: inline-flex; font-size: 10px; font-weight: 700; gap: 5px; left: 10px; padding: 7px 9px; position: absolute; text-transform: uppercase; top: 10px; z-index: 2; }
    .auction-live-now-badge { background: #16a34a; border-radius: 999px; box-shadow: 0 8px 20px rgba(22, 163, 74, .25); color: #fff; font-size: 11px; font-weight: 700; padding: 6px 10px; position: absolute; right: 12px; text-transform: uppercase; top: 12px; z-index: 2; }
    .auction-card--upcoming .auction-card__badge { background: #d99613; }
    .auction-card--closed .auction-card__badge, .auction-card--waiting .auction-card__badge { background: #5e6b61; }
    .auction-card--won .auction-card__badge { background: #0b6fcb; }
    .auction-card__heart { display: none; }
    .auction-card__body { padding: 14px; }
    .auction-card__body h3 { font-size: 18px; font-weight: 700; line-height: 1.25; margin: 0 0 8px; }
    .auction-card__body p { color: #666b72; font-size: 13px; line-height: 1.45; margin: 9px 0 12px; min-height: 56px; }
    .auction-card__tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .auction-card__tags span { border: 1px solid #5f9bea; border-radius: 5px; color: #1769c2; font-size: 10px; font-weight: 700; padding: 4px 7px; text-transform: uppercase; }
    .auction-card__meta { border-top: 1px solid #e4edf9; display: grid; gap: 10px 12px; grid-template-columns: 1fr 1fr; padding-top: 12px; }
    .auction-card__meta span, .auction-bid-summary span { color: #68706b; display: block; font-size: 10px; font-weight: 700; text-transform: uppercase; }
    .auction-card__meta strong { color: #1769c2; display: block; font-size: 16px; margin-top: 3px; }
    .auction-card__closing-date { grid-column: 1 / -1; }
    .auction-card__closing-date strong small { color: #68706b; display: block; font-size: 12px; font-weight: 700; margin-top: 2px; }
    .auction-card__actions { display: grid; gap: 10px; grid-template-columns: 1fr 1fr; margin-top: 14px; }
    .auction-card__actions--single { grid-template-columns: 1fr; }
    .auction-card__actions--single .auction-btn { width: 100%; }
    .auction-btn { align-items: center; border-radius: 7px; display: inline-flex; font-size: 13px; font-weight: 700; gap: 6px; justify-content: center; min-height: 40px; padding: 8px 10px; text-decoration: none; }
    .auction-btn--primary { background: #1769c2; border: 1px solid #1769c2; color: #fff; }
    .auction-btn--primary:hover { background: #1056a3; color: #fff; text-decoration: none; }
    .auction-btn--outline { background: #fff; border: 1px solid #1769c2; color: #1769c2; }
    .auction-btn--outline:hover { background: #eff6ff; color: #1056a3; text-decoration: none; }
    .auction-btn--danger { border-color: #dc2626; color: #dc2626; }
    .auction-btn--danger:hover { background: #fef2f2; color: #b91c1c; }
    .auction-btn--muted { background: #f1f5f9; border: 1px solid #d8e2ee; color: #667085; }
    .auction-empty { align-items: center; background: #fff; border: 1px dashed #c8d9f1; border-radius: 8px; color: #657067; display: flex; flex-direction: column; grid-column: 1 / -1; justify-content: center; min-height: 230px; padding: 30px; text-align: center; }
    .auction-empty svg { color: #1769c2; height: 42px; margin-bottom: 10px; width: 42px; }
    .auction-notifications { display: grid; gap: 12px; }
    .auction-notification { align-items: center; background: #fff; border: 1px solid #dce8f8; border-left: 4px solid #c9d9ed; border-radius: 8px; display: grid; gap: 16px; grid-template-columns: minmax(0, 1fr) auto; padding: 18px; }
    .auction-notification.is-unread { border-left-color: #1769c2; }
    .auction-notification.is-read { background: #f1f5f9; border-color: #e2e8f0; border-left-color: #cbd5e1; opacity: .72; }
    .auction-notification h3 { font-size: 17px; margin: 2px 0 6px; }
    .auction-notification p { color: #66706a; margin: 0 0 4px; }
    .auction-notification.is-read h3, .auction-notification.is-read p { color: #64748b; }
    .auction-notification span, .auction-notification small { color: #1769c2; font-size: 12px; font-weight: 700; text-transform: uppercase; }
    .auction-notification.is-read span, .auction-notification.is-read small { color: #64748b; }
    .auction-notification__actions { align-items: center; display: flex; flex-wrap: nowrap; gap: 8px; justify-content: flex-end; }
    .auction-notification__actions form { margin: 0; }
    .auction-notification__actions .auction-btn { min-width: 92px; white-space: nowrap; }
    .auction-bid-summary { background: #f5f9ff; border: 1px solid #dce8f8; border-radius: 8px; display: grid; gap: 12px; grid-template-columns: repeat(3, 1fr); padding: 14px; }
    .auction-bid-summary strong { color: #1769c2; display: block; font-size: 18px; margin-top: 4px; }
    .auction-bid-modal .modal-dialog { max-width: 560px; }
    .auction-bid-modal .modal-content { border: 0; border-radius: 8px; box-shadow: 0 24px 70px rgba(16, 35, 63, .24); overflow: hidden; }
    .auction-bid-modal .modal-header { align-items: flex-start; background: #fff; border-bottom: 1px solid #e5eefb; padding: 20px 22px 16px; }
    .auction-bid-modal .modal-title { color: #10233f; font-size: 18px; font-weight: 800; line-height: 1.25; margin-top: 4px; }
    .auction-bid-modal .modal-body { background: linear-gradient(180deg, #f8fbff 0%, #fff 48%); padding: 20px 22px; }
    .auction-bid-modal .modal-footer { background: #fff; border-top: 1px solid #e5eefb; gap: 10px; padding: 16px 22px; }
    .auction-bid-product { align-items: center; background: #fff; border: 1px solid #dbe8f8; border-radius: 8px; display: flex; justify-content: center; margin-bottom: 16px; min-height: 180px; padding: 16px; }
    .auction-bid-product img { border-radius: 7px; display: block; max-height: 180px; object-fit: contain; width: 100%; }
    .auction-bid-product.is-empty { display: none; }
    .auction-bid-info { display: grid; gap: 10px; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-bottom: 14px; }
    .auction-bid-info div { background: #fff; border: 1px solid #dbe8f8; border-radius: 8px; padding: 12px; }
    .auction-bid-info small { color: #68706b; display: block; font-size: 10px; font-weight: 800; letter-spacing: .03em; line-height: 1.25; margin-bottom: 5px; text-transform: uppercase; }
    .auction-bid-info strong { color: #1769c2; display: block; font-size: 15px; font-weight: 800; line-height: 1.3; }
    .auction-bid-tags { display: flex; flex-wrap: wrap; gap: 6px; margin: 0 0 16px; }
    .auction-bid-tags:empty { display: none; }
    .auction-bid-tags span { background: #f3f9f4; border: 1px solid #a8d8b5; border-radius: 999px; color: #17643a; font-size: 11px; font-weight: 800; padding: 5px 9px; text-transform: uppercase; }
    .auction-bid-modal .form-label { color: #263749; display: block; font-size: 13px; margin-bottom: 8px; }
    .auction-bid-modal .form-control { border: 1px solid #cfdef0; border-radius: 7px; min-height: 48px; }
    .auction-bid-modal .form-control:focus { border-color: #1769c2; box-shadow: 0 0 0 3px rgba(23, 105, 194, .12); }
    .auction-bid-help { color: #68706b; display: block; font-size: 12px; margin-top: 8px; }
    .auction-detail { background: #fff; border: 1px solid #dce8f8; border-radius: 8px; overflow: hidden; }
    .auction-detail__gallery { align-items: center; background: #fff; border-right: 1px solid #e4edf9; display: flex; justify-content: center; min-height: 300px; }
    .auction-detail__gallery img { max-height: 300px; object-fit: contain; padding: 24px; width: 100%; }
    .auction-detail__content { padding: 26px; }
    .auction-detail__content h2 { font-size: 30px; font-weight: 700; margin: 10px 0; }
    .auction-detail__description { background: #fff; border: 1px solid #dce8f8; border-radius: 8px; margin-top: 22px; padding: 24px; }
    @media (max-width: 1199px) { .auction-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767px) {
        .auction-dashboard__intro { align-items: stretch; flex-direction: column; }
        .auction-notification { align-items: stretch; grid-template-columns: 1fr; }
        .auction-notification__actions { justify-content: flex-start; overflow-x: auto; }
        .auction-grid, .auction-card__actions { grid-template-columns: 1fr; }
        .auction-bid-summary, .auction-bid-info { grid-template-columns: 1fr; }
        .auction-bid-modal .modal-dialog { margin: 12px; }
        .auction-bid-product { min-height: 150px; }
    }
</style>
