<div class="card">
  <div class="flex items-center justify-between border-b px-5 py-4">
    <div>
      <h2 class="card-title">Son yazılar</h2>
      <p class="card-description mt-1.5">Blog modülünden</p>
    </div>
    <a href="<?= Router::url('admin.blog.index') ?>" class="btn btn-secondary btn-sm">Tümü</a>
  </div>
  <?php if (!$posts): ?>
    <p class="px-5 py-10 text-center text-sm text-muted-foreground">Henüz yazı yok.</p>
  <?php else: ?>
    <table class="table">
      <tbody>
        <?php foreach ($posts as $post): ?>
          <tr>
            <td class="font-medium"><a href="<?= Router::url('admin.blog.edit', ['id' => $post['id']]) ?>" class="hover:underline hover:underline-offset-4"><?= View::e($post['title']) ?></a></td>
            <td class="w-0 whitespace-nowrap text-right text-muted-foreground"><?= View::date($post['created_at']) ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  <?php endif ?>
</div>
