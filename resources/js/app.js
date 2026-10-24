import './echo';

document.addEventListener('DOMContentLoaded', () => {
    const familyMeta = document.querySelector('meta[name="family-id"]');
    const userMeta = document.querySelector('meta[name="user-id"]');
    const familyId = familyMeta ? familyMeta.getAttribute('content') : null;
    const currentUserId = userMeta ? Number(userMeta.getAttribute('content')) : null;

    if (!familyId || !window.Echo) {
        return;
    }

    const channel = window.Echo.private(`family.${familyId}`);

    const handleEvent = (event, fallbackMsg) => {
        const isSelf = event && event.userId && currentUserId && Number(event.userId) === currentUserId;

        // Skip toast for the action performer, but always reload data across all tabs & sessions
        if (!isSelf && typeof window.showToast === 'function') {
            window.showToast(event.message || fallbackMsg, 'info');
        }

        if (typeof window.reloadCurrentModuleData === 'function') {
            window.reloadCurrentModuleData(event);
        }
    };

    const bindEvent = (eventName, fallbackMsg) => {
        channel
            .listen(eventName, (e) => handleEvent(e, fallbackMsg))
            .listen(`.${eventName}`, (e) => handleEvent(e, fallbackMsg));
    };

    bindEvent('ChoreEvent', 'Ada pembaruan tugas keluarga.');
    bindEvent('FinanceEvent', 'Ada pembaruan kas atau anggaran keluarga.');
    bindEvent('CalendarEvent', 'Ada pembaruan agenda keluarga.');
    bindEvent('TimelineEvent', 'Ada momen kenangan keluarga baru.');
    bindEvent('FamilyMemberEvent', 'Ada perubahan status anggota keluarga.');
    bindEvent('NoteEvent', 'Ada pembaruan catatan di papan keluarga.');
});
