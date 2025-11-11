<?php
class ClaimsController {
    public static function store(): void {
        require_login();
        verify_csrf();

        $item_id = (int)($_POST['item_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        if ($item_id <= 0) { set_flash('error','Invalid item.'); redirect('index.php'); }

        $item = Item::findApprovedById($item_id);
        if (!$item) { set_flash('error','Item not found.'); redirect('index.php'); }

        if ($item['type'] !== 'found') { set_flash('error','Claims are only for found items.'); redirect('index.php?r=items/show&id='.$item_id); }
        if ($item['state'] !== 'open') { set_flash('error','This item is not open for claims.'); redirect('index.php?r=items/show&id='.$item_id); }
        if ((int)$item['user_id'] === current_user_id()) { set_flash('error','You cannot claim your own item.'); redirect('index.php?r=items/show&id='.$item_id); }

        if (Claim::pendingByUserAndItem(current_user_id(), $item_id)) {
            set_flash('error','You already have a pending claim for this item.');
            redirect('index.php?r=items/show&id='.$item_id);
        }

        $claimId = Claim::create($item_id, current_user_id(), $message !== '' ? $message : null);
        Item::updateState($item_id, 'claim_initiated');

        // Notify item owner (notifying poster)
        $actor = current_user();
        $title = 'Claim initiated on your found item';
        $body  = ($actor['name'] ?: $actor['school_id']).' started a claim.';
        $url   = base_url('index.php?r=items/show&id='.$item_id);
        Notification::create((int)$item['user_id'], 'claim_new', 'item', $item_id, $title, $body, $url);

        set_flash('success', 'Claim submitted. An administrator will review it.');
        redirect('index.php?r=items/show&id='.$item_id);
    }

    public static function cancel(): void {
        require_login();
        verify_csrf();

        $claim_id = (int)($_POST['claim_id'] ?? 0);
        $item_id  = (int)($_POST['item_id'] ?? 0);

        if ($claim_id <= 0 || $item_id <= 0) { set_flash('error','Invalid request.'); redirect('index.php'); }

        $ok = Claim::cancel($claim_id, current_user_id());
        if ($ok) {
            if (Claim::pendingCountByItem($item_id) === 0) {
                Item::updateState($item_id, 'open');
            }
            set_flash('success','Your claim was cancelled.');
        } else {
            set_flash('error','Unable to cancel the claim.');
        }
        redirect('index.php?r=items/show&id='.$item_id);
    }
}