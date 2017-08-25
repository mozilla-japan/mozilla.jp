        </div><!-- end #doc -->
      </div><!-- end #doc-outer -->
<?php if (!empty($breadcrumbs)): ?>
      <nav id="breadcrumbs" role="navigation" aria-label="パンくずリスト" itemprop="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
        <p><?= $mj->build_breadcrumbs($breadcrumbs); ?></p>
      </nav>
<?php endif; ?>
      <footer id="pagefooter" role="contentinfo" itemscope itemtype="http://schema.org/WPFooter">
        <div id="globalfooter" class="outer">
          <div id="copyright" class="inner">
            <p>Copyright &#169; <span itemprop="copyrightYear"><?=date('Y');?></span> <span itemprop="copyrightHolder" itemscope itemtype="http://schema.org/Organization"><span itemprop="name">Mozilla Japan Community</span></span>. All rights reserved. <?php if (!empty($extra_footer_links)): echo $extra_footer_links; endif; ?></p>
          </div>
        </div><!-- end #globalfooter -->
      </footer><!-- end #pagefooter -->
    </div><!-- end #wrapper -->
<?php
require_once dirname(__FILE__) . '/shared-footer.inc.php';
