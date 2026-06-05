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
    .auction-card__badge { align-items: center; background: #1769c2; border-radius: 999px; color: #fff; display: inline-flex; font-size: 10px; font-weight: 700; gap: 5px; left: 10px; padding: 7px 9px; position: absolute; text-transform: uppercase; top: 10px; z-index: 2; }
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
    .auction-card__actions { display: grid; gap: 10px; grid-template-columns: 1fr 1fr; margin-top: 14px; }
    .auction-btn { align-items: center; border-radius: 7px; display: inline-flex; font-size: 13px; font-weight: 700; gap: 6px; justify-content: center; min-height: 40px; padding: 8px 10px; text-decoration: none; }
    .auction-btn--primary { background: #1769c2; border: 1px solid #1769c2; color: #fff; }
    .auction-btn--primary:hover { background: #1056a3; color: #fff; text-decoration: none; }
    .auction-btn--outline { background: #fff; border: 1px solid #1769c2; color: #1769c2; }
    .auction-btn--outline:hover { background: #eff6ff; color: #1056a3; text-decoration: none; }
    .auction-btn--muted { background: #f1f5f9; border: 1px solid #d8e2ee; color: #667085; }
    .auction-empty { align-items: center; background: #fff; border: 1px dashed #c8d9f1; border-radius: 8px; color: #657067; display: flex; flex-direction: column; grid-column: 1 / -1; justify-content: center; min-height: 230px; padding: 30px; text-align: center; }
    .auction-empty svg { color: #1769c2; height: 42px; margin-bottom: 10px; width: 42px; }
    .auction-notifications { display: grid; gap: 12px; }
    .auction-notification { align-items: center; background: #fff; border: 1px solid #dce8f8; border-left: 4px solid #c9d9ed; border-radius: 8px; display: flex; gap: 16px; justify-content: space-between; padding: 18px; }
    .auction-notification.is-unread { border-left-color: #1769c2; }
    .auction-notification h3 { font-size: 17px; margin: 2px 0 6px; }
    .auction-notification p { color: #66706a; margin: 0 0 4px; }
    .auction-notification span, .auction-notification small { color: #1769c2; font-size: 12px; font-weight: 700; text-transform: uppercase; }
    .auction-notification__actions { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; }
    .auction-bid-summary { background: #f5f9ff; border: 1px solid #dce8f8; border-radius: 8px; display: grid; gap: 12px; grid-template-columns: repeat(3, 1fr); padding: 14px; }
    .auction-bid-summary strong { color: #1769c2; display: block; font-size: 18px; margin-top: 4px; }
    .auction-detail { background: #fff; border: 1px solid #dce8f8; border-radius: 8px; overflow: hidden; }
    .auction-detail__gallery { align-items: center; background: #fff; border-right: 1px solid #e4edf9; display: flex; justify-content: center; min-height: 300px; }
    .auction-detail__gallery img { max-height: 300px; object-fit: contain; padding: 24px; width: 100%; }
    .auction-detail__content { padding: 26px; }
    .auction-detail__content h2 { font-size: 30px; font-weight: 700; margin: 10px 0; }
    .auction-detail__description { background: #fff; border: 1px solid #dce8f8; border-radius: 8px; margin-top: 22px; padding: 24px; }
    @media (max-width: 1199px) { .auction-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767px) {
        .auction-dashboard__intro, .auction-notification { align-items: stretch; flex-direction: column; }
        .auction-grid, .auction-card__actions { grid-template-columns: 1fr; }
        .auction-bid-summary { grid-template-columns: 1fr; }
    }
</style>
