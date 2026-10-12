<li>
    <a href="?id=<?=$id;?>"><?=h($category['title']);?></a>
    <?php if(isset($category['childs'])): ?>
        <ul>
            <?=$this->getMenuHtml($category['childs']); ?>
        </ul>
    <?php endif; ?>
</li>