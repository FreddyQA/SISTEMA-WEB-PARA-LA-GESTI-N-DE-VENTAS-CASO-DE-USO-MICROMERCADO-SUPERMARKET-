import JsBarcode from 'jsbarcode';
import QRCode from 'qrcode';

window.JsBarcode = JsBarcode;
window.QRCode = QRCode;
window.__codeLibrariesReady = true;
window.__codeLibrariesPromise = Promise.resolve(true);
window.dispatchEvent(new CustomEvent('code-libraries-ready'));
