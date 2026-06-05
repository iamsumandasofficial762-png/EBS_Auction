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
    .auction-grid { display: grid; gap: 22px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .auction-card { background: #fff; border: 1px solid #d7e4d2; border-radius: 8px; box-shadow: 0 16px 38px rgba(25, 61, 30, .08); overflow: hidden; }
    .auction-card__media { aspect-ratio: 1 / .72; background: radial-gradient(circle at center, #6f936c 0, #294e2d 70%); position: relative; }
    .auction-card__media img { height: 100%; object-fit: contain; padding: 32px; width: 100%; }
    .auction-card__badge { align-items: center; background: #1f5f2b; border-radius: 999px; color: #fff; display: inline-flex; font-size: 12px; font-weight: 700; gap: 6px; left: 18px; padding: 9px 13px; position: absolute; text-transform: uppercase; top: 18px; z-index: 2; }
    .auction-card--upcoming .auction-card__badge { background: #d99613; }
    .auction-card--closed .auction-card__badge, .auction-card--waiting .auction-card__badge { background: #5e6b61; }
    .auction-card--won .auction-card__badge { background: #0b8c56; }
    .auction-card__heart { align-items: center; background: rgba(23, 70, 31, .82); border: 0; border-radius: 50%; color: #fff; display: inline-flex; height: 44px; justify-content: center; position: absolute; right: 18px; top: 18px; width: 44px; z-index: 2; }
    .auction-card__body { padding: 22px; }
    .auction-card__body h3 { font-size: 22px; font-weight: 700; margin: 0 0 10px; }
    .auction-card__body p { color: #666b72; line-height: 1.55; margin: 12px 0 18px; min-height: 66px; }
    .auction-card__tags { display: flex; flex-wrap: wrap; gap: 8px; }
    .auction-card__tags span { border: 1px solid #27602f; border-radius: 6px; color: #1f5f2b; font-size: 12px; font-weight: 700; padding: 5px 9px; text-transform: uppercase; }
    .auction-card__meta { border-top: 1px solid #e4e9e2; display: grid; gap: 16px; grid-template-columns: 1fr 1fr; padding-top: 18px; }
    .auction-card__meta span, .auction-bid-summary span { color: #68706b; display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .auction-card__meta strong { color: #1e5b27; display: block; font-size: 19px; margin-top: 4px; }
    .auction-card__actions { display: grid; gap: 12px; grid-template-columns: 1fr 1fr; margin-top: 20px; }
    .auction-btn { align-items: center; border-radius: 7px; display: inline-flex; font-weight: 700; gap: 8px; justify-content: center; min-height: 46px; padding: 10px 14px; text-decoration: none; }
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
    .auction-detail__gallery { background: radial-gradient(circle at center, #73936e, #294e2d 70%); min-height: 360px; }
    .auction-detail__gallery img { height: 100%; min-height: 360px; object-fit: contain; padding: 34px; width: 100%; }
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
