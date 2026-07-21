package com.rentone.user.presentation.feature.account.historybalance.adapter
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.RecyclerView
import com.rentone.user.R
import com.rentone.user.core.ui.LoadingFooterListAdapter
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.databinding.ItemListTopupBinding
import com.rentone.user.domain.model.Topup

class ListTopupAdapter(
    private val onItemClick: (Topup) -> Unit
) : LoadingFooterListAdapter<Topup>(DIFF_CALLBACK) {

    override fun onCreateItemViewHolder(parent: ViewGroup): RecyclerView.ViewHolder =
        TopupViewHolder(ItemListTopupBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindItemViewHolder(holder: RecyclerView.ViewHolder, item: Topup, position: Int) {
        (holder as TopupViewHolder).bind(item)
    }

    inner class TopupViewHolder(private val binding: ItemListTopupBinding) : RecyclerView.ViewHolder(binding.root) {
        fun bind(item: Topup) {
            val ctx = binding.root.context
            binding.txtDate.text = ViewUtils.mysqlDateToNormalDate(item.date.orEmpty(), "yyyy-MM-dd HH:mm:ss", "dd MMM yyyy HH:mm")
            binding.txtStatus.text = item.statusName

            binding.txtCompanyBank.text = if (item.bankName == null) {
                ctx.getString(R.string.bank_info_not_found)
            } else {
                "${item.bankName} - ${item.name}"
            }
            binding.txtValue.text = "Rp. ${ViewUtils.formatCurrency(item.valueWithCode)},-"

            binding.itemContainer.setOnClickListener(null)
            if (item.status == 1) {
                binding.itemContainer.setOnClickListener { onItemClick(item) }
            }
        }
    }

    private companion object {
        val DIFF_CALLBACK = object : DiffUtil.ItemCallback<Topup>() {
            override fun areItemsTheSame(oldItem: Topup, newItem: Topup) = oldItem.id == newItem.id
            override fun areContentsTheSame(oldItem: Topup, newItem: Topup) = oldItem == newItem
        }
    }
}
