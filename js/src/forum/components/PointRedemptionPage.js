import app from 'flarum/forum/app';
import Page from 'flarum/common/components/Page';
import Button from 'flarum/common/components/Button';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';

export default class PointRedemptionPage extends Page {
  oninit(vnode) {
    super.oninit(vnode);
    this.code = '';
    this.balance = 0;
    this.redemptions = [];
    this.loading = true;
    this.submitting = false;
    this.message = null;
    this.messageType = null;
    this.load();
  }

  apiUrl(path) {
    return app.forum.attribute('apiUrl') + path;
  }

  async load() {
    this.loading = true;

    try {
      const response = await app.request({
        method: 'GET',
        url: this.apiUrl('/point-redemption/me/redemptions'),
      });

      this.redemptions = Array.isArray(response.data) ? response.data : [];
      this.balance = Number(response.meta?.balance || 0);
    } catch (error) {
      this.message = this.errorMessage(error);
      this.messageType = 'error';
    } finally {
      this.loading = false;
      m.redraw();
    }
  }

  async redeem(event) {
    event.preventDefault();

    if (this.submitting) return;

    if (!this.code.trim()) {
      this.message = app.translator.trans('lowseekai-point-redempt.forum.errors.empty_code');
      this.messageType = 'error';
      m.redraw();
      return;
    }

    this.submitting = true;
    this.message = null;

    try {
      const response = await app.request({
        method: 'POST',
        url: this.apiUrl('/point-redemption/redeem'),
        body: {
          data: {
            attributes: {
              code: this.code.trim(),
              requestId: this.requestId(),
            },
          },
        },
      });

      this.balance = Number(response.meta?.balance || this.balance);
      this.redemptions = [response.data, ...this.redemptions].slice(0, 50);
      this.code = '';
      this.message = app.translator.trans('lowseekai-point-redempt.forum.redeem_success', {
        points: response.data?.attributes?.pointsAmount || 0,
      });
      this.messageType = 'success';
    } catch (error) {
      this.message = this.errorMessage(error);
      this.messageType = 'error';
    } finally {
      this.submitting = false;
      m.redraw();
    }
  }

  requestId() {
    if (window.crypto?.randomUUID) return window.crypto.randomUUID();

    return `redemption-${Date.now()}-${Math.random().toString(36).slice(2)}`;
  }

  errorMessage(error) {
    const detail = error?.response?.errors?.[0]?.detail || error?.response?.errors?.[0]?.title;

    return detail || error?.message || app.translator.trans('lowseekai-point-redempt.forum.errors.generic');
  }

  view() {
    return (
      <main className="PointRedemptionPage">
        <div className="container">
          <div className="PointRedemptionPage-header">
            <div>
              <h1>{app.translator.trans('lowseekai-point-redempt.forum.title')}</h1>
              <p className="helpText">{app.translator.trans('lowseekai-point-redempt.forum.subtitle')}</p>
            </div>
            <div className="PointRedemptionPage-balance">
              <span>{app.translator.trans('lowseekai-point-redempt.forum.current_balance')}</span>
              <strong>{this.balance}</strong>
            </div>
          </div>

          <section className="PointRedemptionPage-section">
            <h2>{app.translator.trans('lowseekai-point-redempt.forum.redeem_heading')}</h2>
            <form className="Form PointRedemptionPage-form" onsubmit={this.redeem.bind(this)}>
              <div className="Form-group">
                <label for="point-redemption-code">
                  {app.translator.trans('lowseekai-point-redempt.forum.code_label')}
                </label>
                <input
                  id="point-redemption-code"
                  className="FormControl"
                  type="text"
                  autocomplete="off"
                  spellcheck="false"
                  placeholder={app.translator.trans('lowseekai-point-redempt.forum.code_placeholder')}
                  value={this.code}
                  oninput={(event) => {
                    this.code = event.target.value;
                  }}
                />
              </div>
              <div className="Form-group Form-controls">
                <Button
                  type="submit"
                  className="Button Button--primary"
                  icon="fas fa-gift"
                  loading={this.submitting}
                  disabled={this.submitting}
                >
                  {app.translator.trans('lowseekai-point-redempt.forum.redeem_button')}
                </Button>
              </div>
            </form>
            {this.message && <div className={`PointRedemptionPage-message PointRedemptionPage-message--${this.messageType}`}>{this.message}</div>}
          </section>

          <section className="PointRedemptionPage-section">
            <h2>{app.translator.trans('lowseekai-point-redempt.forum.history_heading')}</h2>
            {this.loading ? (
              <LoadingIndicator />
            ) : this.redemptions.length ? (
              <div className="PointRedemptionPage-tableWrap">
                <table className="Table PointRedemptionPage-table">
                  <thead>
                    <tr>
                      <th>{app.translator.trans('lowseekai-point-redempt.forum.batch')}</th>
                      <th>{app.translator.trans('lowseekai-point-redempt.forum.points')}</th>
                      <th>{app.translator.trans('lowseekai-point-redempt.forum.code_suffix')}</th>
                      <th>{app.translator.trans('lowseekai-point-redempt.forum.redeemed_at')}</th>
                    </tr>
                  </thead>
                  <tbody>
                    {this.redemptions.map((redemption) => {
                      const attributes = redemption.attributes || {};

                      return (
                        <tr key={redemption.id}>
                          <td>{attributes.batchName || '-'}</td>
                          <td>+{attributes.pointsAmount}</td>
                          <td>••••{attributes.codeSuffix}</td>
                          <td>{attributes.redeemedAt ? new Date(attributes.redeemedAt).toLocaleString() : '-'}</td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            ) : (
              <p className="helpText">{app.translator.trans('lowseekai-point-redempt.forum.history_empty')}</p>
            )}
          </section>
        </div>
      </main>
    );
  }
}
