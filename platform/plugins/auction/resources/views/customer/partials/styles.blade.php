<style>
    .d-none { display: none !important; }
    .auction-dashboard { color: #122015; }
    .auction-dashboard__intro { align-items: center; background: linear-gradient(135deg, #eff8ea, #ffffff); border: 1px solid #d7e8cf; border-radius: 8px; display: flex; justify-content: space-between; margin-bottom: 20px; padding: 22px; }
    .auction-dashboard__intro h2 { font-size: 28px; font-weight: 700; margin: 4px 0 8px; }
    .auction-dashboard__intro p { color: #66706a; margin: 0; max-width: 720px; }
    .auction-kicker { color: #1d5f2a; font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .auction-tabs { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 22px; }
    .auction-tab { align-items: center; background: #fff; border: 1px solid #dfe7dc; border-radius: 8px; color: #1e2d22; display: inline-flex; gap: 8px; padding: 10px 14px; text-decoration: none; }
    .auction-tab:hover, .auction-tab.is-active { background: #1e5b27; border-color: #1e5b27; color: #fff; text-decoration: none; }
    .auction-tab strong { align-items: center; background: rgba(30, 91, 39, .1); border-radius: 999px; display: inline-flex; font-size: 12px; height: 24px; justify-content: center; min-width: 24px; padding: 0 7px; }
    .auction-tab.is-active strong, .auction-tab:hover strong { background: rgba(255, 255, 255, .18); }
    .auction-grid { display: grid; gap: 18px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .auction-card { background: #fff; border: 1px solid #d7e4d2; border-radius: 8px; box-shadow: 0 10px 24px rgba(25, 61, 30, .06); overflow: hidden; }
    .auction-card__media { align-items: center; background: #fff; border-bottom: 1px solid #edf1ea; display: flex; height: 138px; justify-content: center; padding: 12px; position: relative; }
    .auction-card__media img { height: 100%; object-fit: contain; padding: 0; width: 100%; }
    .auction-card__badge { align-items: center; background: #1f5f2b; border-radius: 999px; color: #fff; display: inline-flex; font-size: 10px; font-weight: 700; gap: 5px; left: 10px; padding: 7px 9px; position: absolute; text-transform: uppercase; top: 10px; z-index: 2; }
    .auction-card--upcoming .auction-card__badge { background: #d99613; }
    .auction-card--closed .auction-card__badge, .auction-card--waiting .auction-card__badge { background: #5e6b61; }
    .auction-card--won .auction-card__badge { background: #0b8c56; }
    .auction-card__heart { display: none; }
    .auction-card__body { padding: 14px; }
    .auction-card__body h3 { font-size: 18px; font-weight: 700; line-height: 1.25; margin: 0 0 8px; }
    .auction-card__body p { color: #666b72; font-size: 13px; line-height: 1.45; margin: 9px 0 12px; min-height: 56px; }
    .auction-card__tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .auction-card__tags span { border: 1px solid #27602f; border-radius: 5px; color: #1f5f2b; font-size: 10px; font-weight: 700; padding: 4px 7px; text-transform: uppercase; }
    .auction-card__meta { border-top: 1px solid #e4e9e2; display: grid; gap: 10px 12px; grid-template-columns: 1fr 1fr; padding-top: 12px; }
    .auction-card__meta span, .auction-bid-summary span { color: #68706b; display: block; font-size: 10px; font-weight: 700; text-transform: uppercase; }
    .auction-card__meta strong { color: #1e5b27; display: block; font-size: 16px; margin-top: 3px; }
    .auction-card__actions { display: grid; gap: 10px; grid-template-columns: 1fr 1fr; margin-top: 14px; }
    .auction-btn { align-items: center; border-radius: 7px; display: inline-flex; font-size: 13px; font-weight: 700; gap: 6px; justify-content: center; min-height: 40px; padding: 8px 10px; text-decoration: none; }
    .auction-btn--primary { background: #1e5b27; border: 1px solid #1e5b27; color: #fff; }
    .auction-btn--primary:hover { background: #17491f; color: #fff; text-decoration: none; }
    .auction-btn--outline { background: #fff; border: 1px solid #1e5b27; color: #1e5b27; }
    .auction-btn--outline:hover { background: #edf7e9; color: #1e5b27; text-decoration: none; }
    .auction-btn--muted { background: #eef2ed; border: 1px solid #dbe3d8; color: #6c756d; }
    .auction-empty { align-items: center; background: #fff; border: 1px dashed #cbdac7; border-radius: 8px; color: #657067; display: flex; flex-direction: column; grid-column: 1 / -1; justify-content: center; min-height: 230px; padding: 30px; text-align: center; }
    .auction-empty svg { color: #1e5b27; height: 42px; margin-bottom: 10px; width: 42px; }
    .auction-notifications { display: grid; gap: 12px; }
    .auction-notification { align-items: center; background: #fff; border: 1px solid #dfe7dc; border-left: 4px solid #cdd8c9; border-radius: 8px; display: flex; gap: 16px; justify-content: space-between; padding: 18px; }
    .auction-notification.is-unread { border-left-color: #1e5b27; }
    .auction-notification h3 { font-size: 17px; margin: 2px 0 6px; }
    .auction-notification p { color: #66706a; margin: 0 0 4px; }
    .auction-notification span, .auction-notification small { color: #1e5b27; font-size: 12px; font-weight: 700; text-transform: uppercase; }
    .auction-notification__actions { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; }
    .auction-bid-summary { background: #f3f8f0; border: 1px solid #dfeada; border-radius: 8px; display: grid; gap: 12px; grid-template-columns: repeat(3, 1fr); padding: 14px; }
    .auction-bid-summary strong { color: #1e5b27; display: block; font-size: 18px; margin-top: 4px; }
    .auction-detail { background: #fff; border: 1px solid #dfe7dc; border-radius: 8px; overflow: hidden; }
    .auction-detail__gallery { align-items: center; background: #fff; border-right: 1px solid #e4e9e2; display: flex; justify-content: center; min-height: 300px; }
    .auction-detail__gallery img { max-height: 300px; object-fit: contain; padding: 24px; width: 100%; }
    .auction-detail__content { padding: 26px; }
    .auction-detail__content h2 { font-size: 30px; font-weight: 700; margin: 10px 0; }
    .auction-detail__description { background: #fff; border: 1px solid #dfe7dc; border-radius: 8px; margin-top: 22px; padding: 24px; }
    @media (max-width: 1199px) { .auction-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767px) {
        .auction-dashboard__intro, .auction-notification { align-items: stretch; flex-direction: column; }
        .auction-grid, .auction-card__actions { grid-template-columns: 1fr; }
        .auction-bid-summary { grid-template-columns: 1fr; }
    }
</style>
