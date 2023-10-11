declare module 'breezeTypes' {

  interface UtilsProps {
    smfVars: smfVars
  }

  type ToastCallback = () => void;

  interface NoticeOptions {
    message: string
    appearance: string
    autoDismiss: boolean
  }
}

module.exports = {
  UtilsProps,
  ToastCallback,
  NoticeOptions,
};
