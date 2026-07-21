package com.rentone.user.presentation.feature.news.detail
import android.content.ClipData
import android.content.ClipboardManager
import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import coil.load
import com.rentone.user.R
import com.rentone.user.domain.model.NewsDetail
import com.rentone.user.core.common.Config
import com.rentone.user.core.common.UiState
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.databinding.ActivityNewsDetailBinding
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class NewsDetailActivity : AppCompatActivity() {

    private lateinit var binding: ActivityNewsDetailBinding
    private val viewModel: NewsDetailViewModel by viewModels()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityNewsDetailBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        observeState()
        viewModel.loadDetail(intent.getIntExtra("id", 0))
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        viewModel.loadDetail(intent.getIntExtra("id", 0))
    }

    private fun setupData(data: NewsDetail) {
        val news = data.news
        val voucher = data.voucher

        binding.txtTitle.text = news.title
        title = news.title

        if (news.img != null) {
            binding.imagePreview.isVisible = true
            binding.imagePreview.load(Config.BASE_NEWS_IMAGE + news.img) { error(R.drawable.no_image) }
        } else {
            binding.imagePreview.isVisible = false
        }

        if (news.isVoucher == 1 && voucher != null) {
            binding.voucherContainer.isVisible = true
            binding.txtCode.text = voucher.code

            binding.btnReferalCopy.setOnClickListener {
                val clipboard = getSystemService(Context.CLIPBOARD_SERVICE) as ClipboardManager
                val clip = ClipData.newPlainText(getString(R.string.voucher_code_message), voucher.code)
                clipboard.setPrimaryClip(clip)
                Toast.makeText(this, R.string.voucher_code_message, Toast.LENGTH_LONG).show()
            }

            if (voucher.useExpire == 1 || voucher.useQuota == 1) {
                binding.infoVoucherContainer.isVisible = true

                binding.txtDateRange.isVisible = voucher.useExpire == 1
                if (voucher.useExpire == 1) {
                    binding.txtDateRange.text =
                        "${ViewUtils.mysqlDateToNormalDate(voucher.startDate.orEmpty(), "yyyy-MM-dd", "dd MMM yyyy")} - " +
                            ViewUtils.mysqlDateToNormalDate(voucher.endDate.orEmpty(), "yyyy-MM-dd", "dd MMM yyyy")
                }

                binding.txtQuota.isVisible = voucher.useQuota == 1
                if (voucher.useQuota == 1) {
                    binding.txtQuota.text = getString(R.string.voucher_quota_left, voucher.quota.toString())
                }
            } else {
                binding.infoVoucherContainer.isVisible = false
            }
        } else {
            binding.voucherContainer.isVisible = false
        }

        binding.wvContent.settings.javaScriptEnabled = true
        binding.wvContent.loadDataWithBaseURL("", news.content.orEmpty(), "text/html", "UTF-8", "")
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.detail.collect { state ->
                    when (state) {
                        is UiState.Success -> setupData(state.data)
                        is UiState.Error -> {
                            AlertDialog.Builder(this@NewsDetailActivity)
                                .setTitle(getString(R.string.menu_news))
                                .setMessage(getString(R.string.failed_check_to_server))
                                .setCancelable(false)
                                .setPositiveButton(R.string.yes) { _, _ -> finish() }
                                .show()
                        }
                        else -> Unit
                    }
                }
            }
        }
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
        }
        return true
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
