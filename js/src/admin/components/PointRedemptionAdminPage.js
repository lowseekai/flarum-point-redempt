import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Button from 'flarum/common/components/Button';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import CreateBatchModal from './CreateBatchModal';

export default class PointRedemptionAdminPage extends ExtensionPage {
  oninit(vnode) {
    super.oninit(vnode);
    const displayName = String(app.data.settings?.['point-redempt.display_name'] || '').trim();

    if (displayName && this.extension?.extra?.['flarum-extension']) {
      this.extension.extra['flarum-extension'].title = displayName;
    }

    this.batches = [];
    this.redemptions = [];
    this.loadingBatches = true;
    this.loadingRedemptions = true;
    this.batchError = null;
    this.redemptionError = null;
    this.loadBatches();
    this.loadRedemptions();
  }

  content(vnode) {
    const displayName = String(app.data.settings?.['point-redempt.display_name'] || '').trim();

    return [
      super.content(vnode),
      <div className="PointRedemptionAdmin">
        <div className="container">
          <div className="PointRedemptionAdmin-toolbar">
            <div>
              <h3>
                {displayName || app.translator.trans('lowseekai-point-redempt.forum.title')}
                {' · '}
                {app.translator.trans('lowseekai-point-redempt.admin.batches_title')}
              </h3>
              <p className="helpText">{app.translator.trans('lowseekai-point-redempt.admin.batches_help')}</p>
            </div>
            <Button
              className="Button Button--primary"
              icon="fas fa-plus"
              onclick={() => app.modal.show(CreateBatchModal, { onCreated: () => this.loadBatches() })}
            >
              {app.translator.trans('lowseekai-point-redempt.admin.create_button')}
            </Button>
          </div>

          {this.batchError && <div className="PointRedemptionAdmin-error">{this.batchError}</div>}
          {this.loadingBatches ? <LoadingIndicator /> : this.batchTable()}

          <div className="PointRedemptionAdmin-records">
            <h3>{app.translator.trans('lowseekai-point-redempt.admin.records_title')}</h3>
            {this.redemptionError && <div className="PointRedemptionAdmin-error">{this.redemptionError}</div>}
            {this.loadingRedemptions ? <LoadingIndicator /> : this.redemptionTable()}
          </div>
        </div>
      </div>,
    ];
  }

  batchTable() {
    return this.batches.length ? (
      <div className="PointRedemptionAdmin-tableWrap">
        <table className="Table PointRedemptionAdmin-table">
          <thead>
            <tr>
              <th>{app.translator.trans('lowseekai-point-redempt.admin.columns.name')}</th>
              <th>{app.translator.trans('lowseekai-point-redempt.admin.columns.points')}</th>
              <th>{app.translator.trans('lowseekai-point-redempt.admin.columns.quantity')}</th>
              <th>{app.translator.trans('lowseekai-point-redempt.admin.columns.validity')}</th>
              <th>{app.translator.trans('lowseekai-point-redempt.admin.columns.status')}</th>
              <th>{app.translator.trans('lowseekai-point-redempt.admin.columns.actions')}</th>
            </tr>
          </thead>
          <tbody>
            {this.batches.map((resource) => {
              const batch = resource.attributes || {};
              const busy = this.toggling === resource.id;

              return (
                <tr key={resource.id}>
                  <td>
                    <strong>{batch.name}</strong>
                    {batch.note && <div className="helpText">{batch.note}</div>}
                  </td>
                  <td>{batch.pointsAmount}</td>
                  <td>
                    {batch.redeemedCount} / {batch.quantity}
                  </td>
                  <td>
                    <div>{this.formatDate(batch.startsAt)}</div>
                    <div>{batch.expiresAt ? this.formatDate(batch.expiresAt) : app.translator.trans('lowseekai-point-redempt.admin.permanent')}</div>
                  </td>
                  <td>
                    <span className={`PointRedemptionAdmin-status PointRedemptionAdmin-status--${batch.status}`}>
                      {this.statusLabel(batch.status)}
                    </span>
                  </td>
                  <td>
                    <Button
                      className={batch.isEnabled ? 'Button Button--danger' : 'Button Button--primary'}
                      icon={batch.isEnabled ? 'fas fa-pause' : 'fas fa-play'}
                      loading={busy}
                      disabled={busy || ['expired', 'exhausted'].includes(batch.status)}
                      onclick={() => this.toggleBatch(resource.id, !batch.isEnabled)}
                    >
                      {batch.isEnabled
                        ? app.translator.trans('lowseekai-point-redempt.admin.disable')
                        : app.translator.trans('lowseekai-point-redempt.admin.enable')}
                    </Button>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    ) : (
      <p className="helpText">{app.translator.trans('lowseekai-point-redempt.admin.empty_batches')}</p>
    );
  }

  redemptionTable() {
    return this.redemptions.length ? (
      <div className="PointRedemptionAdmin-tableWrap">
        <table className="Table PointRedemptionAdmin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>{app.translator.trans('lowseekai-point-redempt.admin.columns.batch')}</th>
              <th>{app.translator.trans('lowseekai-point-redempt.admin.columns.user')}</th>
              <th>{app.translator.trans('lowseekai-point-redempt.admin.columns.points')}</th>
              <th>{app.translator.trans('lowseekai-point-redempt.admin.columns.code_suffix')}</th>
              <th>{app.translator.trans('lowseekai-point-redempt.admin.columns.redeemed_at')}</th>
            </tr>
          </thead>
          <tbody>
            {this.redemptions.map((resource) => {
              const redemption = resource.attributes || {};

              return (
                <tr key={resource.id}>
                  <td>#{resource.id}</td>
                  <td>{redemption.batchName || '-'}</td>
                  <td>{redemption.username || redemption.userId || '-'}</td>
                  <td>+{redemption.pointsAmount}</td>
                  <td>****{redemption.codeSuffix}</td>
                  <td>{this.formatDate(redemption.redeemedAt)}</td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    ) : (
      <p className="helpText">{app.translator.trans('lowseekai-point-redempt.admin.empty_records')}</p>
    );
  }

  async loadBatches() {
    this.loadingBatches = true;

    try {
      const response = await app.request({
        method: 'GET',
        url: app.forum.attribute('apiUrl') + '/point-redemption/batches',
      });
      this.batches = Array.isArray(response.data) ? response.data : [];
    } catch (error) {
      this.batchError = this.errorMessage(error);
    } finally {
      this.loadingBatches = false;
      m.redraw();
    }
  }

  async loadRedemptions() {
    this.loadingRedemptions = true;

    try {
      const response = await app.request({
        method: 'GET',
        url: app.forum.attribute('apiUrl') + '/point-redemption/redemptions',
      });
      this.redemptions = Array.isArray(response.data) ? response.data : [];
    } catch (error) {
      this.redemptionError = this.errorMessage(error);
    } finally {
      this.loadingRedemptions = false;
      m.redraw();
    }
  }

  async toggleBatch(id, isEnabled) {
    this.toggling = id;

    try {
      await app.request({
        method: 'PATCH',
        url: app.forum.attribute('apiUrl') + `/point-redemption/batches/${id}`,
        body: { data: { attributes: { isEnabled } } },
      });
      await this.loadBatches();
    } catch (error) {
      this.batchError = this.errorMessage(error);
    } finally {
      this.toggling = null;
      m.redraw();
    }
  }

  statusLabel(status) {
    return app.translator.trans(`lowseekai-point-redempt.admin.status.${status}`);
  }

  formatDate(value) {
    return value ? new Date(value).toLocaleString() : '-';
  }

  errorMessage(error) {
    const detail = error?.response?.errors?.[0]?.detail || error?.response?.errors?.[0]?.title;

    return detail || error?.message || app.translator.trans('lowseekai-point-redempt.admin.error');
  }
}
