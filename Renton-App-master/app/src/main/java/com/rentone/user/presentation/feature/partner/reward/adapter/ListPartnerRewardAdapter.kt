package com.rentone.user.presentation.feature.partner.reward.adapter
import android.content.Context
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import android.widget.LinearLayout
import androidx.core.view.isVisible
import coil.load
import com.rentone.user.R
import com.rentone.user.core.common.Config
import com.rentone.user.databinding.ItemListPartnerRewardBinding
import com.rentone.user.databinding.ItemPartnerRewardBinding
import com.rentone.user.domain.model.PartnerReward
import com.rentone.user.domain.model.Reward

class ListPartnerRewardAdapter(
    private val ctx: Context,
    list: ArrayList<PartnerReward>,
    private val onClaimClick: (Reward) -> Unit
) : ArrayAdapter<PartnerReward>(ctx, 0, list) {

    private val dataList: List<PartnerReward> = list

    override fun getView(position: Int, convertView: View?, parent: ViewGroup): View {
        val binding = if (convertView == null) {
            ItemListPartnerRewardBinding.inflate(LayoutInflater.from(ctx), parent, false)
        } else {
            ItemListPartnerRewardBinding.bind(convertView)
        }

        val data = dataList[position]
        binding.txtFeatureName.text = data.featureName

        val listRewardContainer = LinearLayout(ctx).apply {
            layoutParams = LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT)
            orientation = LinearLayout.HORIZONTAL
        }

        binding.txtMessage.isVisible = data.rewards.isEmpty()

        data.rewards.forEach { reward ->
            listRewardContainer.addView(buildRewardChip(parent, reward))
        }

        binding.rewardContainer.removeAllViews()
        binding.rewardContainer.addView(listRewardContainer)

        return binding.root
    }

    private fun buildRewardChip(parent: ViewGroup, reward: Reward): View {
        val chip = ItemPartnerRewardBinding.inflate(LayoutInflater.from(ctx), parent, false)

        if (reward.aquired == 1) {
            chip.item.alpha = 1.0f
        }

        chip.txtTitle.text = reward.title
        chip.previewImage.load(Config.BASE_PARTNER_REWARD_IMAGE + reward.img) { error(R.drawable.no_image) }
        chip.txtTarget.text = "${reward.target} ${ctx.getString(R.string.transaction)}"

        chip.txtPointReward.isVisible = reward.pointReward > 0
        if (reward.pointReward > 0) {
            chip.txtPointReward.text = "${reward.pointReward} ${ctx.getString(R.string.point)}"
        }

        val canClaim = reward.rewardType == 2 && reward.aquired == 1 && reward.claimed == 0 && reward.processed == 0
        chip.btnClaim.isVisible = canClaim
        chip.btnClaim.setOnClickListener { onClaimClick(reward) }

        chip.txtStatus.isVisible = reward.claimed == 1
        if (reward.claimed == 1) {
            chip.txtStatus.text = ctx.getString(
                if (reward.processed == 0) R.string.waiting_processed else R.string.on_proccess
            )
        }

        return chip.root
    }
}
