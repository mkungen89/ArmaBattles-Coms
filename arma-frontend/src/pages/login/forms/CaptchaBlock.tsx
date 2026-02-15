import HCaptcha from "@hcaptcha/react-hcaptcha";
import { observer } from "mobx-react-lite";

import styles from "../Login.module.scss";
import { Text } from "preact-i18n";
import { useEffect } from "preact/hooks";

import { Preloader } from "@revoltchat/ui";

import { clientController } from "../../../controllers/client/ClientController";

export interface CaptchaProps {
    onSuccess: (token?: string) => void;
    onCancel: () => void;
}

export const CaptchaBlock = observer((props: CaptchaProps) => {
    const configuration = clientController.getServerConfig();

    useEffect(() => {
        if (!configuration?.features.captcha.enabled) {
            props.onSuccess();
        }
    }, [configuration?.features.captcha.enabled, props]);

    if (!configuration?.features.captcha.enabled)
        return <Preloader type="spinner" />;

    return (
        <div className={styles.captcha}>
            <div className={styles.title}>Are you human?</div>
            <div className={styles.accessibilityNote}>
                <Text id="login.captcha_accessibility_note" />
            </div>
            <div
                className={styles.checkbox}
                role="region"
                aria-label="Human verification challenge">
                <HCaptcha
                    theme="dark"
                    sitekey={configuration.features.captcha.key}
                    onVerify={(token) => props.onSuccess(token)}
                />
            </div>
            <a onClick={props.onCancel} role="button" tabIndex={0}>
                <Text id="login.cancel" />
            </a>
        </div>
    );
});
